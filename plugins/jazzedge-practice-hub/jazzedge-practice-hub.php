<?php
/**
 * Plugin Name: JazzEdge Practice Hub
 * Plugin URI: https://jazzedge.com
 * Description: A neuroscience-backed practice system for online piano learning sites, incorporating spaced repetition, gamification, and AI analysis.
 * Version: 2.0.0
 * Author: JazzEdge
 * Author URI: https://jazzedge.com
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
define('JPH_VERSION', '2.0.0');
define('JPH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JPH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('JPH_PLUGIN_BASENAME', plugin_basename(__FILE__));

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
        
        // Register AJAX handlers
        add_action('wp_ajax_jph_award_first_steps_badge', array($this, 'ajax_award_first_steps_badge'));
        
        // REST API handles database operations (no AJAX needed)
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
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
                    <p><em>Coming Soon - Katahdin AI Hub integration needed</em></p>
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
            console.log('🔑 REST API Nonce for testing:', nonce);
            console.log('📋 Test URLs:');
            console.log('GET  https://support.jazzedge.com/wp-json/jph/v1/test');
            console.log('POST https://support.jazzedge.com/wp-json/jph/v1/database/test-create (NO AUTH)');
            console.log('GET  https://support.jazzedge.com/wp-json/jph/v1/database/check-tables (AUTH REQUIRED)');
            console.log('POST https://support.jazzedge.com/wp-json/jph/v1/database/create-tables (AUTH REQUIRED)');
            console.log('GET  https://support.jazzedge.com/wp-json/jph/v1/database/schema (AUTH REQUIRED)');
            console.log('🔧 Use this header: X-WP-Nonce: ' + nonce);
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
                    if (data.katahdin_hub_available) {
                        katahdinStatus.innerHTML = '✅ Available';
                        katahdinStatus.style.color = 'green';
                    } else {
                        katahdinStatus.innerHTML = '❌ Not Available';
                        katahdinStatus.style.color = 'red';
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
                    <span class="jph-modal-close" onclick="closeViewStudentModal()">&times;</span>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="badges-table-body">
                            <tr>
                                <td colspan="6" class="jph-loading">Loading badges...</td>
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
                    <span class="jph-modal-close" onclick="closeAddBadgeModal()">&times;</span>
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
                            <label for="badge-rarity">Rarity:</label>
                            <select id="badge-rarity" name="rarity">
                                <option value="common">Common</option>
                                <option value="rare">Rare</option>
                                <option value="epic">Epic</option>
                                <option value="legendary">Legendary</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-xp-reward">XP Reward:</label>
                            <input type="number" id="badge-xp-reward" name="xp_reward" min="0" value="0">
                        </div>
                        
                        <div class="jph-form-actions">
                            <button type="button" class="button button-primary" onclick="addBadge()">Create Badge</button>
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
                    <span class="jph-modal-close" onclick="closeEditStudentModal()">&times;</span>
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
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .jph-modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .jph-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        
        .jph-modal-close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
            line-height: 1;
        }
        
        .jph-modal-close:hover {
            color: #333;
        }
        
        .jph-modal-body {
            padding: 20px;
        }
        
        .jph-student-detail-section {
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
                    <span class="toast-close">&times;</span>
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
            
            // Run migrations button
            const runMigrationsBtn = document.getElementById('run-migrations-btn');
            if (runMigrationsBtn) {
                runMigrationsBtn.addEventListener('click', function() {
                    runMigrations();
                });
            }
            
            // Test badge awarding button
            const testBadgeAwardingBtn = document.getElementById('test-badge-awarding-btn');
            if (testBadgeAwardingBtn) {
                testBadgeAwardingBtn.addEventListener('click', function() {
                    testBadgeAwarding();
                });
            }
            
            // Test webhook button
            const testWebhookBtn = document.getElementById('test-webhook-btn');
            if (testWebhookBtn) {
                testWebhookBtn.addEventListener('click', function() {
                    document.getElementById('webhook-test-section').style.display = 'block';
                });
            }
            
            // Close webhook test button
            const closeWebhookTestBtn = document.getElementById('close-webhook-test-btn');
            if (closeWebhookTestBtn) {
                closeWebhookTestBtn.addEventListener('click', function() {
                    document.getElementById('webhook-test-section').style.display = 'none';
                });
            }
            
            // Send test webhook button
            const sendTestWebhookBtn = document.getElementById('send-test-webhook-btn');
            if (sendTestWebhookBtn) {
                sendTestWebhookBtn.addEventListener('click', function() {
                    sendTestWebhook();
                });
            }
            
            // Sync badge count button
            const syncBadgeCountBtn = document.getElementById('sync-badge-count-btn');
            if (syncBadgeCountBtn) {
                syncBadgeCountBtn.addEventListener('click', function() {
                    syncBadgeCount();
                });
            }
            
            // Sync all badge counts button
            const syncAllBadgeCountsBtn = document.getElementById('sync-all-badge-counts-btn');
            if (syncAllBadgeCountsBtn) {
                syncAllBadgeCountsBtn.addEventListener('click', function() {
                    syncAllBadgeCounts();
                });
            }
            
            // View webhook log button
            const viewWebhookLogBtn = document.getElementById('view-webhook-log-btn');
            if (viewWebhookLogBtn) {
                viewWebhookLogBtn.addEventListener('click', function() {
                    document.getElementById('webhook-log-section').style.display = 'block';
                    loadWebhookLog();
                });
            }
            
            // Refresh webhook log button
            const refreshWebhookLogBtn = document.getElementById('refresh-webhook-log-btn');
            if (refreshWebhookLogBtn) {
                refreshWebhookLogBtn.addEventListener('click', function() {
                    loadWebhookLog();
                });
            }
            
            // Clear webhook log button
            const clearWebhookLogBtn = document.getElementById('clear-webhook-log-btn');
            if (clearWebhookLogBtn) {
                clearWebhookLogBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to clear the webhook log?')) {
                        clearWebhookLog();
                    }
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
            
            // Form submission handlers removed - using onclick handlers instead
        });
        
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
            console.log('loadBadgesData() called');
            const tbody = document.getElementById('badges-table-body');
            if (!tbody) {
                console.error('badges-table-body not found');
                return;
            }
            tbody.innerHTML = '<tr><td colspan="7" class="jph-loading">Loading badges...</td></tr>';
            
            console.log('Fetching badges from API...');
            fetch('<?php echo rest_url('jph/v1/badges'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Badges API response:', data);
                if (data.success) {
                    console.log('Badges data:', data.badges);
                    renderBadgesTable(data.badges);
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Error loading badges</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading badges:', error);
                tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Error loading badges</td></tr>';
            });
        }
        
        // Render badges table
        function renderBadgesTable(badges) {
            const tbody = document.getElementById('badges-table-body');
            
            console.log('Rendering badges table with data:', badges);
            
            if (badges.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">No badges found</td></tr>';
                return;
            }
            
            tbody.innerHTML = badges.map(badge => `
                <tr class="jph-badge-row ${!badge.is_active ? 'inactive' : ''}">
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
                        <div class="jph-badge-actions">
                            <button class="button button-small button-primary" onclick="editBadge(${badge.id})" title="Edit Badge">
                                ✏️ Edit
                            </button>
                            <button class="button button-small button-link-delete" onclick="deleteBadge(${badge.id})" title="Delete Badge">
                                🗑️ Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
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
        function loadWebhookLog() {
            const logContent = document.getElementById('webhook-log-content');
            if (!logContent) return;
            
            logContent.innerHTML = '<div class="loading-message">Loading webhook log...</div>';
            
            fetch('<?php echo rest_url('jph/v1/webhook-log'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayWebhookLog(data.log_entries);
                } else {
                    logContent.innerHTML = '<div style="color: red;">❌ Error loading webhook log: ' + (data.message || 'Unknown error') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error loading webhook log:', error);
                logContent.innerHTML = '<div style="color: red;">❌ Error loading webhook log: ' + error + '</div>';
            });
        }
        
        // Display webhook log
        function displayWebhookLog(entries) {
            const logContent = document.getElementById('webhook-log-content');
            if (!logContent) return;
            
            if (!entries || entries.length === 0) {
                logContent.innerHTML = '<div style="color: #666;">No webhook log entries found.</div>';
                return;
            }
            
            let html = '';
            entries.forEach(entry => {
                const typeClass = entry.type === 'success' ? 'color: green;' : 
                                 entry.type === 'error' ? 'color: red;' : 
                                 'color: #666;';
                const typeIcon = entry.type === 'success' ? '✅' : 
                                entry.type === 'error' ? '❌' : 
                                'ℹ️';
                
                html += `<div style="margin-bottom: 5px; ${typeClass}">`;
                html += `<span style="color: #999;">[${entry.timestamp}]</span> `;
                html += `<span style="margin-right: 5px;">${typeIcon}</span>`;
                html += `<span>${entry.message}</span>`;
                html += `</div>`;
            });
            
            logContent.innerHTML = html;
        }
        
        // Clear webhook log
        function clearWebhookLog() {
            fetch('<?php echo rest_url('jph/v1/clear-webhook-log'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadWebhookLog();
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error clearing webhook log:', error);
                showToast('Error clearing webhook log: ' + error, 'error');
            });
        }
        
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
        function editBadge(badgeId) {
            // First, get the badge data
            fetch('<?php echo rest_url('jph/v1/badges/'); ?>' + badgeId, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    openEditBadgeModal(data.badge);
                } else {
                    showToast('Error loading badge: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error loading badge:', error);
                showToast('Error loading badge: ' + error, 'error');
            });
        }
        
        // Open edit badge modal
        function openEditBadgeModal(badge) {
            // Populate the edit form with badge data
            document.getElementById('edit-badge-id').value = badge.id;
            document.getElementById('edit-badge-name').value = badge.name || '';
            document.getElementById('edit-badge-description').value = badge.description || '';
            document.getElementById('edit-badge-category').value = badge.category || 'achievement';
            document.getElementById('edit-badge-rarity').value = badge.rarity_level || 'common';
            document.getElementById('edit-badge-xp-reward').value = badge.xp_reward || 0;
            document.getElementById('edit-badge-gem-reward').value = badge.gem_reward || 0;
            document.getElementById('edit-badge-is-active').checked = badge.is_active == 1;
            
            // Criteria fields
            const criteriaTypeEl = document.getElementById('edit-badge-criteria-type');
            const criteriaValueEl = document.getElementById('edit-badge-criteria-value');
            if (criteriaTypeEl) criteriaTypeEl.value = badge.criteria_type || 'manual';
            if (criteriaValueEl) criteriaValueEl.value = badge.criteria_value || 0;
            
            // Webhook field
            const webhookEl = document.getElementById('edit-badge-webhook-url');
            if (webhookEl) webhookEl.value = badge.webhook_url || '';
            
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
            
            
            const badgeId = document.getElementById('edit-badge-id').value;
            
            // Create JSON data instead of FormData
            const badgeData = {
                id: badgeId,
                name: document.getElementById('edit-badge-name').value,
                description: document.getElementById('edit-badge-description').value,
                category: document.getElementById('edit-badge-category').value,
                rarity: document.getElementById('edit-badge-rarity').value,
                xp_reward: parseInt(document.getElementById('edit-badge-xp-reward').value),
                gem_reward: parseInt(document.getElementById('edit-badge-gem-reward').value),
                criteria_type: document.getElementById('edit-badge-criteria-type') ? document.getElementById('edit-badge-criteria-type').value : 'manual',
                criteria_value: document.getElementById('edit-badge-criteria-value') ? parseInt(document.getElementById('edit-badge-criteria-value').value) : 0,
                webhook_url: document.getElementById('edit-badge-webhook-url') ? document.getElementById('edit-badge-webhook-url').value : '',
                is_active: document.getElementById('edit-badge-is-active').checked ? 1 : 0
            };
            
            
            fetch('<?php echo rest_url('jph/v1/badges/'); ?>' + badgeId, {
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
        function deleteBadge(badgeId) {
            if (confirm('Are you sure you want to delete this badge?')) {
                console.log('Deleting badge ID:', badgeId);
                
                fetch('<?php echo rest_url('jph/v1/badges/'); ?>' + badgeId, {
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
        
        // Test webhook
        function sendTestWebhook() {
            const webhookUrl = document.getElementById('test-webhook-url').value;
            const resultDiv = document.getElementById('webhook-test-result');
            
            if (!webhookUrl) {
                resultDiv.innerHTML = '<div style="color: red;">Please enter a webhook URL</div>';
                return;
            }
            
            resultDiv.innerHTML = '<div style="color: blue;">Sending test webhook...</div>';
            
            fetch('<?php echo rest_url('jph/v1/test-webhook'); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    webhook_url: webhookUrl
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Webhook test response:', data);
                if (data.success) {
                    resultDiv.innerHTML = '<div style="color: green;">✅ Webhook sent successfully! Check your webhook URL for the test data.</div>';
                } else {
                    resultDiv.innerHTML = '<div style="color: red;">❌ Webhook failed: ' + (data.message || 'Unknown error') + '</div>';
                }
            })
            .catch(error => {
                console.error('Webhook test error:', error);
                resultDiv.innerHTML = '<div style="color: red;">❌ Webhook test error: ' + error + '</div>';
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
                <button type="button" class="button button-secondary" id="run-migrations-btn" style="background: #ff6b6b; color: white;">🔧 Run Migrations</button>
                <button type="button" class="button button-secondary" id="test-badge-awarding-btn" style="background: #28a745; color: white;">🎯 Test Badge Awarding</button>
                <button type="button" class="button button-secondary" id="test-webhook-btn" style="background: #17a2b8; color: white;">🔗 Test Webhook</button>
                <button type="button" class="button button-secondary" id="sync-badge-count-btn" style="background: #6f42c1; color: white;">🔄 Sync Badge Count</button>
                <button type="button" class="button button-secondary" id="sync-all-badge-counts-btn" style="background: #fd7e14; color: white;">🔄 Sync All Badge Counts</button>
                <button type="button" class="button button-secondary" id="view-webhook-log-btn" style="background: #6c757d; color: white;">📋 View Webhook Log</button>
            </div>
            
            <!-- Webhook Test Section -->
            <div id="webhook-test-section" style="display: none; margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <h4>🔗 Test Webhook</h4>
                <div class="jph-form-group">
                    <label for="test-webhook-url">Webhook URL:</label>
                    <input type="url" id="test-webhook-url" placeholder="https://webhook.site/your-unique-url" style="width: 100%; max-width: 500px;">
                    <small>Enter a webhook URL to test the webhook system</small>
                </div>
                <div class="jph-form-actions">
                    <button type="button" class="button button-primary" id="send-test-webhook-btn">Send Test Webhook</button>
                    <button type="button" class="button button-secondary" id="close-webhook-test-btn">Close</button>
                </div>
                <div id="webhook-test-result" style="margin-top: 10px;"></div>
            </div>
            
            <!-- Webhook Log Section -->
            <div id="webhook-log-section" style="display: none; margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4>📋 Webhook Log</h4>
                    <div>
                        <button type="button" class="button button-small" id="refresh-webhook-log-btn">🔄 Refresh</button>
                        <button type="button" class="button button-small button-link-delete" id="clear-webhook-log-btn">🗑️ Clear Log</button>
                    </div>
                </div>
                <div id="webhook-log-content" style="max-height: 400px; overflow-y: auto; background: #fff; border: 1px solid #ddd; padding: 10px; font-family: monospace; font-size: 12px;">
                    <div class="loading-message">Loading webhook log...</div>
                </div>
            </div>
            
            <div class="jph-badges-table-container">
                <table class="jph-badges-table">
                    <thead>
                        <tr>
                            <th>Badge</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Rarity</th>
                            <th>XP Reward</th>
                            <th>Status</th>
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
                    <span class="jph-modal-close" onclick="closeAddBadgeModal()">&times;</span>
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
                            <label for="badge-rarity">Rarity:</label>
                            <select id="badge-rarity" name="rarity">
                                <option value="common">Common</option>
                                <option value="rare">Rare</option>
                                <option value="epic">Epic</option>
                                <option value="legendary">Legendary</option>
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
                            <small>Meaning depends on criteria type (e.g., XP amount, minutes, count).</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-webhook-url">Webhook URL (optional):</label>
                            <input type="url" id="badge-webhook-url" name="webhook_url" placeholder="https://example.com/webhook">
                            <small>URL to call when this badge is earned. Will receive POST request with badge and user data.</small>
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
                    <span class="jph-modal-close" onclick="closeEditBadgeModal()">&times;</span>
                </div>
                <div class="jph-modal-body">
                    <form id="jph-edit-badge-form" enctype="multipart/form-data">
                        <input type="hidden" id="edit-badge-id" name="id">
                        
                        
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
                            <label for="edit-badge-rarity">Rarity:</label>
                            <select id="edit-badge-rarity" name="rarity">
                                <option value="common">Common</option>
                                <option value="uncommon">Uncommon</option>
                                <option value="rare">Rare</option>
                                <option value="epic">Epic</option>
                                <option value="legendary">Legendary</option>
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
                                <option value="streak_7">7-day streak</option>
                                <option value="streak_30">30-day streak</option>
                                <option value="streak_100">100-day streak</option>
                                <option value="long_session">Long session (≥ minutes)</option>
                                <option value="improvement_count">Improvements reported ≥ value</option>
                                <option value="first_session">First practice session</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-criteria-value">Criteria Value:</label>
                            <input type="number" id="edit-badge-criteria-value" name="criteria_value" min="0" value="0">
                            <small>Meaning depends on criteria type (e.g., XP amount, minutes, count).</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-webhook-url">Webhook URL (optional):</label>
                            <input type="url" id="edit-badge-webhook-url" name="webhook_url" placeholder="https://example.com/webhook">
                            <small>URL to call when this badge is earned. Will receive POST request with badge and user data.</small>
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
            align-items: center;
            justify-content: center;
            padding: 20px;
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
                padding: 10px;
                align-items: flex-start;
                padding-top: 20px;
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
                padding: 5px;
                align-items: flex-start;
                padding-top: 10px;
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
                <div class="jph-settings-section">
                    <h2>🤖 AI Configuration</h2>
                    <p><em>Coming Soon - Katahdin AI Hub integration</em></p>
                </div>
                
                <div class="jph-settings-section">
                    <h2>🎮 Gamification Settings</h2>
                    <p><em>Coming Soon - XP, badges, streaks configuration</em></p>
                </div>
                
                <div class="jph-settings-section">
                    <h2>🔗 Webhooks</h2>
                    <p><em>Coming Soon - External system integration</em></p>
                </div>
                
                <div class="jph-settings-section">
                    <h2>📊 Analytics</h2>
                    <p><em>Coming Soon - Usage tracking and reporting</em></p>
                </div>
            </div>
        </div>
        
        <style>
        .jph-settings-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .jph-settings-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-settings-section h2 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .jph-settings-section p {
            margin: 0;
            color: #666;
            font-style: italic;
        }
        </style>
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
        
        // Badge management endpoints
        register_rest_route('jph/v1', '/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_badge'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        register_rest_route('jph/v1', '/badges/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badge'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
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
                        return is_numeric($param);
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
                        return is_numeric($param);
                    }
                )
            )
        ));
        
        register_rest_route('jph/v1', '/badges/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_badges_stats'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // User badges endpoint
        register_rest_route('jph/v1', '/user-badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_badges'),
            'permission_callback' => '__return_true'
        ));
        
        // Create default badges endpoint
        register_rest_route('jph/v1', '/create-default-badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_create_default_badges'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
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
        register_rest_route('jph/v1', '/debug/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_badges'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/debug/user-stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_user_stats'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('jph/v1', '/debug/database', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_debug_database'),
            'permission_callback' => '__return_true'
        ));
        
        // Check and award badges endpoint
        register_rest_route('jph/v1', '/check-badges', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_check_and_award_badges'),
            'permission_callback' => '__return_true'
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
        
        // Run migrations endpoint
        register_rest_route('jph/v1', '/run-migrations', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_run_migrations'),
            'permission_callback' => '__return_true'
        ));
        
        // Test webhook endpoint
        register_rest_route('jph/v1', '/test-webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_webhook'),
            'permission_callback' => '__return_true'
        ));
        
        // Sync badge count endpoint
        register_rest_route('jph/v1', '/sync-badge-count', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_sync_badge_count'),
            'permission_callback' => '__return_true'
        ));
        
        // Simple webhook connectivity test endpoint
        register_rest_route('jph/v1', '/test-connectivity', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_test_connectivity'),
            'permission_callback' => '__return_true'
        ));
        
        // Sync all badge counts endpoint
        register_rest_route('jph/v1', '/sync-all-badge-counts', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_sync_all_badge_counts'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Webhook log endpoint
        register_rest_route('jph/v1', '/webhook-log', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_webhook_log'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));
        
        // Clear webhook log endpoint
        register_rest_route('jph/v1', '/clear-webhook-log', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_clear_webhook_log'),
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
     * Check user permission (logged in users)
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
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                $user_id = 1; // Fallback to user 1 for testing
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
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                $user_id = 1; // Fallback to user 1 for testing
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
            $gamification->add_xp($user_id, $xp_earned);
            
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
            $user_id = $request->get_param('user_id') ?: 1; // Default to user 1 for testing
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
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            
            if (!$user_id) {
                $user_id = 1; // Fallback to user 1 for testing
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
     * REST API: Get all badges
     */
    public function rest_get_badges($request) {
        try {
            global $wpdb;
            $database = new JPH_Database();
            $badges = $database->get_badges(true); // Get only active badges
            
            // Add awarded count to each badge
            $user_badges_table = $wpdb->prefix . 'jph_user_badges';
            foreach ($badges as &$badge) {
                $awarded_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT user_id) FROM {$user_badges_table} WHERE badge_id = %s",
                    $badge['id']
                ));
                $badge['awarded_count'] = (int) $awarded_count;
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'badges' => $badges,
                'total_count' => count($badges),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get badge by ID
     */
    public function rest_get_badge($request) {
        try {
            $badge_id = $request->get_param('id');
            $database = new JPH_Database();
            $badge = $database->get_badge($badge_id);
            
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
            $badge_key = sanitize_text_field($request->get_param('badge_key'));
            $name = sanitize_text_field($request->get_param('name'));
            $description = sanitize_textarea_field($request->get_param('description'));
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
            
            $badge_data = array(
                'name' => $name,
                'description' => $description,
                'icon' => $image_url ?: '🏆',
                'category' => $category ?: 'achievement',
                'rarity_level' => $rarity ?: 'common',
                'xp_reward' => $xp_reward ?: 0,
                'gem_reward' => $gem_reward ?: 0,
                'criteria_type' => $criteria_type ?: 'manual',
                'criteria_value' => $criteria_value ?: 1,
                'webhook_url' => $webhook_url,
                'is_active' => 1,
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
            $badge_id = intval($request->get_param('id'));
            $database = new JPH_Database();
            
            
            // Validate badge ID
            if (empty($badge_id)) {
                return new WP_Error('missing_badge_id', 'Badge ID is required', array('status' => 400));
            }
            
            // Check if badge exists
            $existing_badge = $database->get_badge($badge_id);
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
            if (isset($json_data['rarity']) && !empty($json_data['rarity'])) {
                $badge_data['rarity_level'] = sanitize_text_field($json_data['rarity']);
            }
            if (isset($json_data['xp_reward']) && $json_data['xp_reward'] >= 0) {
                $badge_data['xp_reward'] = intval($json_data['xp_reward']);
            }
            if (isset($json_data['gem_reward']) && $json_data['gem_reward'] >= 0) {
                $badge_data['gem_reward'] = intval($json_data['gem_reward']);
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
                $filename = sanitize_file_name($badge_id . '.' . $file_extension);
                $file_path = $badge_dir . '/' . $filename;
                
                if (move_uploaded_file($_FILES['badge_image']['tmp_name'], $file_path)) {
                    $badge_data['icon'] = $upload_dir['baseurl'] . '/jph-badges/' . $filename;
                }
            }
            
            $result = $database->update_badge($badge_id, $badge_data);
            
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
            
            if (empty($badge_id)) {
                return new WP_Error('missing_badge_id', 'Badge ID is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Check if badge exists first
            $existing_badge = $database->get_badge($badge_id);
            if (!$existing_badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            $result = $database->delete_badge($badge_id);
            
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
     * Update badge counts for all users after a badge is deleted
     */
    private function update_badge_counts_after_deletion($deleted_badge_id) {
        global $wpdb;
        
        $user_badges_table = $wpdb->prefix . 'jph_user_badges';
        $user_stats_table = $wpdb->prefix . 'jph_user_stats';
        
        // Get all users who had this badge
        $users_with_badge = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$user_badges_table} WHERE badge_id = %s",
            $deleted_badge_id
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
             INNER JOIN {$badges_table} b ON ub.badge_id = b.id 
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
                     INNER JOIN {$badges_table} b ON ub.badge_id = b.id 
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
     * REST API: Get webhook log
     */
    public function rest_get_webhook_log($request) {
        try {
            $log_file = WP_CONTENT_DIR . '/jph-webhook.log';
            
            if (!file_exists($log_file)) {
                return rest_ensure_response(array(
                    'success' => true,
                    'log_entries' => array(),
                    'message' => 'No webhook log file found',
                    'timestamp' => current_time('mysql')
                ));
            }
            
            $log_content = file_get_contents($log_file);
            $log_lines = explode("\n", $log_content);
            
            // Filter and format log entries
            $webhook_entries = array();
            foreach ($log_lines as $line) {
                if (strpos($line, 'JPH Webhook:') !== false) {
                    $webhook_entries[] = array(
                        'timestamp' => $this->extract_timestamp($line),
                        'message' => trim($line),
                        'type' => $this->categorize_webhook_log($line)
                    );
                }
            }
            
            // Reverse to show newest first
            $webhook_entries = array_reverse($webhook_entries);
            
            return rest_ensure_response(array(
                'success' => true,
                'log_entries' => $webhook_entries,
                'total_entries' => count($webhook_entries),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('webhook_log_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Clear webhook log
     */
    public function rest_clear_webhook_log($request) {
        try {
            $log_file = WP_CONTENT_DIR . '/jph-webhook.log';
            
            if (file_exists($log_file)) {
                file_put_contents($log_file, '');
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Webhook log cleared successfully',
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('clear_webhook_log_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Extract timestamp from log line
     */
    private function extract_timestamp($line) {
        if (preg_match('/\[(.*?)\]/', $line, $matches)) {
            return $matches[1];
        }
        return 'Unknown';
    }
    
    /**
     * Categorize webhook log entry
     */
    private function categorize_webhook_log($line) {
        if (strpos($line, 'Successfully sent') !== false) {
            return 'success';
        } elseif (strpos($line, 'Failed to send') !== false) {
            return 'error';
        } elseif (strpos($line, 'Error:') !== false) {
            return 'error';
        } elseif (strpos($line, 'Attempting to trigger') !== false) {
            return 'info';
        } elseif (strpos($line, 'Found webhook URL') !== false) {
            return 'info';
        } elseif (strpos($line, 'Created payload') !== false) {
            return 'info';
        } else {
            return 'info';
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
            
            return rest_ensure_response(array(
                'success' => true,
                'stats' => array(
                    'total_badges' => (int) $total_badges,
                    'active_badges' => (int) $active_badges,
                    'category_count' => $category_count,
                    'total_awards' => (int) $total_awards,
                    'categories' => $categories
                ),
                'timestamp' => current_time('mysql')
            ));
        } catch (Exception $e) {
            return new WP_Error('get_badges_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Get user's badges
     */
    public function rest_get_user_badges($request) {
        try {
            $user_id = get_current_user_id() ?: 1; // Default to user 1 for testing
            
            $database = new JPH_Database();
            
            // Get user's earned badges
            $user_badges = $database->get_user_badges($user_id);
            
            // Get all available badges
            $all_badges = $database->get_badges(true); // Only active badges
            
            // DEBUG: Add debug information
            $debug_info = array(
                'user_id' => $user_id,
                'user_badges_raw' => $user_badges,
                'all_badges_raw' => $all_badges,
                'user_badges_count' => count($user_badges),
                'all_badges_count' => count($all_badges)
            );
            
            // Create a map of earned badges by badge_id
            $earned_badges_map = array();
            foreach ($user_badges as $earned_badge) {
                $earned_badges_map[$earned_badge['badge_id']] = $earned_badge;
            }
            
            // Combine all badges with earned status
            $badges_with_status = array();
            foreach ($all_badges as $badge) {
                $is_earned = isset($earned_badges_map[$badge['id']]);
                $badge_data = $badge;
                $badge_data['is_earned'] = $is_earned;
                
                if ($is_earned) {
                    // Use earned_date instead of earned_at
                    $badge_data['earned_at'] = $earned_badges_map[$badge['id']]['earned_date'];
                }
                
                $badges_with_status[] = $badge_data;
            }
            
            $debug_info['earned_badges_map'] = $earned_badges_map;
            $debug_info['badges_with_status'] = $badges_with_status;
            
            return rest_ensure_response(array(
                'success' => true,
                'badges' => $badges_with_status,
                'earned_count' => count($user_badges),
                'total_count' => count($all_badges),
                'timestamp' => current_time('mysql'),
                'debug' => $debug_info
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
        $earned_badge_ids = array_column($earned_badges, 'badge_id');
        
        $newly_awarded = array();
        
        foreach ($all_badges as $badge) {
            // Skip if already earned
            if (in_array($badge['id'], $earned_badge_ids)) {
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
                    
                case 'first_session':
                    if ($user_stats['total_sessions'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'long_session':
                    // Check if user has any session >= 60 minutes
                    foreach ($sessions as $session) {
                        if ($session['duration_minutes'] >= 60) {
                            $should_award = true;
                            break;
                        }
                    }
                    break;
                    
                case 'improvement_count':
                    // Count sessions with improvement detected
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
                    
                case 'streak_7':
                    if ($user_stats['current_streak'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'streak_30':
                    if ($user_stats['current_streak'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
                    
                case 'streak_100':
                    if ($user_stats['current_streak'] >= $criteria_value) {
                        $should_award = true;
                    }
                    break;
            }
            
            if ($should_award) {
                // Award the badge
                $database->award_badge(
                    $user_id,
                    $badge['id'], // Use badge ID as the key
                    $badge['name'],
                    $badge['description'],
                    $badge['icon'] ?? ''
                );
                
                // Update user stats with XP reward and badge count
                $update_data = array();
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = $user_stats['total_xp'] + $badge['xp_reward'];
                }
                $update_data['badges_earned'] = $user_stats['badges_earned'] + 1;
                $database->update_user_stats($user_id, $update_data);
                
                $newly_awarded[] = $badge;
            }
        }
        
        return $newly_awarded;
    }
    
    /**
     * Create default badges if none exist
     */
    public function create_default_badges() {
        $database = new JPH_Database();
        
        // Check if any badges exist
        $existing_badges = $database->get_badges(false);
        if (!empty($existing_badges)) {
            return; // Badges already exist
        }
        
        $default_badges = array(
            array(
                'badge_key' => 'first_session',
                'name' => 'First Steps',
                'description' => 'Complete your first practice session',
                'category' => 'practice',
                'rarity' => 'common',
                'xp_reward' => 50
            ),
            array(
                'badge_key' => 'marathon',
                'name' => 'Marathon',
                'description' => 'Practice for 60+ minutes in one session',
                'category' => 'achievement',
                'rarity' => 'rare',
                'xp_reward' => 75
            ),
            array(
                'badge_key' => 'rising_star',
                'name' => 'Rising Star',
                'description' => 'Report improvement 10 times',
                'category' => 'improvement',
                'rarity' => 'rare',
                'xp_reward' => 200
            ),
            array(
                'badge_key' => 'hot_streak',
                'name' => 'Hot Streak',
                'description' => 'Practice for 7 days in a row',
                'category' => 'streak',
                'rarity' => 'epic',
                'xp_reward' => 100
            ),
            array(
                'badge_key' => 'lightning',
                'name' => 'Lightning',
                'description' => 'Practice for 30 days in a row',
                'category' => 'streak',
                'rarity' => 'epic',
                'xp_reward' => 500
            ),
            array(
                'badge_key' => 'legend',
                'name' => 'Legend',
                'description' => 'Practice for 100 days in a row',
                'category' => 'streak',
                'rarity' => 'legendary',
                'xp_reward' => 1000
            )
        );
        
        foreach ($default_badges as $badge_data) {
            $database->add_badge($badge_data);
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
     * REST API: Test webhook
     */
    public function rest_test_webhook($request) {
        try {
            error_log("JPH Webhook Test: Starting webhook test");
            
            $json_data = json_decode($request->get_body(), true);
            error_log("JPH Webhook Test: JSON data received: " . print_r($json_data, true));
            
            $webhook_url = sanitize_url($json_data['webhook_url'] ?? '');
            error_log("JPH Webhook Test: Webhook URL: " . $webhook_url);
            
            if (empty($webhook_url)) {
                error_log("JPH Webhook Test: Missing webhook URL");
                return new WP_Error('missing_webhook_url', 'Webhook URL is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Create test payload
            $test_payload = array(
                'event' => 'test_webhook',
                'timestamp' => current_time('mysql'),
                'badge' => array(
                    'id' => 'test',
                    'name' => 'Test Badge',
                    'description' => 'This is a test webhook',
                    'icon' => '🧪',
                    'xp_reward' => 100,
                    'gem_reward' => 10,
                    'category' => 'test',
                    'rarity_level' => 'common'
                ),
                'user' => array(
                    'id' => get_current_user_id(),
                    'email' => wp_get_current_user()->user_email,
                    'display_name' => wp_get_current_user()->display_name
                ),
                'message' => 'This is a test webhook to verify the webhook system is working correctly.'
            );
            
            error_log("JPH Webhook Test: Created test payload: " . json_encode($test_payload));
            
            // Send the webhook
            $result = $database->send_webhook_request($webhook_url, $test_payload);
            
            error_log("JPH Webhook Test: Send result: " . ($result ? 'true' : 'false'));
            
            if ($result) {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Test webhook sent successfully',
                    'webhook_url' => $webhook_url,
                    'timestamp' => current_time('mysql')
                ));
            } else {
                return new WP_Error('webhook_failed', 'Failed to send webhook', array('status' => 500));
            }
        } catch (Exception $e) {
            error_log("JPH Webhook Test: Exception: " . $e->getMessage());
            return new WP_Error('webhook_error', 'Error: ' . $e->getMessage(), array('status' => 500));
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
     * REST API: Test connectivity
     */
    public function rest_test_connectivity($request) {
        try {
            $json_data = json_decode($request->get_body(), true);
            $test_url = sanitize_url($json_data['url'] ?? '');
            
            if (empty($test_url)) {
                return new WP_Error('missing_url', 'URL is required', array('status' => 400));
            }
            
            error_log("JPH Connectivity Test: Testing {$test_url}");
            
            // Test basic connectivity
            $response = wp_remote_get($test_url, array('timeout' => 10));
            
            if (is_wp_error($response)) {
                error_log("JPH Connectivity Test: Failed - " . $response->get_error_message());
                return rest_ensure_response(array(
                    'success' => false,
                    'message' => 'Connectivity test failed: ' . $response->get_error_message(),
                    'url' => $test_url,
                    'timestamp' => current_time('mysql')
                ));
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                
                error_log("JPH Connectivity Test: Success - Code: {$response_code}");
                
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Connectivity test successful',
                    'url' => $test_url,
                    'response_code' => $response_code,
                    'response_length' => strlen($response_body),
                    'timestamp' => current_time('mysql')
                ));
            }
        } catch (Exception $e) {
            error_log("JPH Connectivity Test: Exception - " . $e->getMessage());
            return new WP_Error('connectivity_error', 'Error: ' . $e->getMessage(), array('status' => 500));
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
    
    /**
     * REST API: Debug badges system
     */
    public function rest_debug_badges($request) {
        try {
            $user_id = get_current_user_id() ?: 1;
            $database = new JPH_Database();
            $gamification = new JPH_Gamification();
            global $wpdb;
            
            // Get table names safely
            $table_names = $database->get_table_names();
            $user_badges_table = $table_names['user_badges'];
            $badges_table = $table_names['badges'];
            
            // Get all debug information
            $debug_info = array(
                'user_id' => $user_id,
                'timestamp' => current_time('mysql'),
                'user_stats' => $gamification->get_user_stats($user_id),
                'user_badges_raw' => $database->get_user_badges($user_id),
                'all_badges_raw' => $database->get_badges(true),
                'badge_count_in_stats' => $gamification->get_user_stats($user_id)['badges_earned'],
                'actual_badge_count' => count($database->get_user_badges($user_id)),
                'database_queries' => array(
                    'user_badges_query' => "SELECT * FROM {$user_badges_table} WHERE user_id = {$user_id}",
                    'all_badges_query' => "SELECT * FROM {$badges_table} WHERE is_active = 1"
                )
            );
            
            // Check if earned_date column exists (the actual column name)
            $column_check = $wpdb->get_results("SHOW COLUMNS FROM {$user_badges_table} LIKE 'earned_date'");
            $debug_info['earned_date_column_exists'] = !empty($column_check);
            $debug_info['earned_date_column_info'] = $column_check;
            
            // Get table structure
            $table_structure = $wpdb->get_results("DESCRIBE {$user_badges_table}");
            $debug_info['user_badges_table_structure'] = $table_structure;
            
            return rest_ensure_response($debug_info);
        } catch (Exception $e) {
            return new WP_Error('debug_badges_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Debug user stats
     */
    public function rest_debug_user_stats($request) {
        try {
            $user_id = get_current_user_id() ?: 1;
            $gamification = new JPH_Gamification();
            
            $debug_info = array(
                'user_id' => $user_id,
                'timestamp' => current_time('mysql'),
                'user_stats' => $gamification->get_user_stats($user_id),
                'stats_source' => 'JPH_Gamification::get_user_stats()'
            );
            
            return rest_ensure_response($debug_info);
        } catch (Exception $e) {
            return new WP_Error('debug_user_stats_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Debug database
     */
    public function rest_debug_database($request) {
        try {
            $user_id = get_current_user_id() ?: 1;
            $database = new JPH_Database();
            global $wpdb;
            
            // Get table names safely
            $table_names = $database->get_table_names();
            
            $debug_info = array(
                'user_id' => $user_id,
                'timestamp' => current_time('mysql'),
                'table_names' => $table_names,
                'tables_exist' => $database->tables_exist(),
                'user_badges_count' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_names['user_badges']} WHERE user_id = {$user_id}"),
                'all_badges_count' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_names['badges']} WHERE is_active = 1"),
                'user_stats_exists' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_names['user_stats']} WHERE user_id = {$user_id}") > 0,
                'database_errors' => $wpdb->last_error
            );
            
            return rest_ensure_response($debug_info);
        } catch (Exception $e) {
            return new WP_Error('debug_database_error', 'Error: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * REST API: Check and award badges
     */
    public function rest_check_and_award_badges($request) {
        try {
            $user_id = get_current_user_id() ?: 1; // Default to user 1 for testing
            
            $newly_awarded = $this->check_and_award_badges($user_id);
            
            // Add debug information
            $database = new JPH_Database();
            $gamification = new JPH_Gamification();
            $user_stats = $gamification->get_user_stats($user_id);
            $sessions = $database->get_all_practice_sessions($user_id); // Get all sessions for debug
            $all_badges = $database->get_badges(true);
            $earned_badges = $database->get_user_badges($user_id);
            
            return rest_ensure_response(array(
                'success' => true,
                'newly_awarded' => $newly_awarded,
                'count' => count($newly_awarded),
                'message' => count($newly_awarded) > 0 ? 'New badges awarded!' : 'No new badges to award',
                'debug' => array(
                    'user_stats' => $user_stats,
                    'total_sessions' => count($sessions),
                    'total_badges' => count($all_badges),
                    'earned_badges' => count($earned_badges),
                    'badge_criteria' => array_map(function($badge) {
                        return array(
                            'id' => $badge['id'],
                            'name' => $badge['name'],
                            'criteria_type' => $badge['criteria_type'],
                            'criteria_value' => $badge['criteria_value']
                        );
                    }, $all_badges)
                ),
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
                    'badge_id' => $first_steps_badge['id'],
                    'badge_name' => $first_steps_badge['name'],
                    'criteria_type' => $first_steps_badge['criteria_type'],
                    'criteria_value' => $first_steps_badge['criteria_value'],
                    'user_sessions' => $user_stats['total_sessions'],
                    'should_award' => $should_award,
                    'already_earned' => false
                );
                
                // Check if already earned
                foreach ($earned_badges as $earned) {
                    if ($earned['badge_id'] == $first_steps_badge['id']) {
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
            $badge_id = $request->get_param('badge_id');
            
            if (!$badge_id) {
                return new WP_Error('missing_badge_id', 'Badge ID is required', array('status' => 400));
            }
            
            $database = new JPH_Database();
            
            // Get the badge details
            $badge = $database->get_badge($badge_id);
            if (!$badge) {
                return new WP_Error('badge_not_found', 'Badge not found', array('status' => 404));
            }
            
            // Check if user already has this badge
            $user_badges = $database->get_user_badges($user_id);
            foreach ($user_badges as $earned_badge) {
                if ($earned_badge['badge_id'] == $badge_id) {
                    return new WP_Error('badge_already_earned', 'User already has this badge', array('status' => 400));
                }
            }
            
            // Award the badge
            $result = $database->award_badge(
                $user_id,
                $badge_id,
                $badge['name'],
                $badge['description'],
                $badge['icon'] ?? ''
            );
            
            if ($result) {
                // Add XP reward and update badge count
                    $gamification = new JPH_Gamification();
                    $user_stats = $gamification->get_user_stats($user_id);
                $update_data = array();
                if ($badge['xp_reward'] > 0) {
                    $update_data['total_xp'] = $user_stats['total_xp'] + intval($badge['xp_reward']);
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
                // Add XP reward and update badge count
                $gamification = new JPH_Gamification();
                $user_stats = $gamification->get_user_stats($user_id);
                $new_xp = $user_stats['total_xp'] + 50;
                $new_badge_count = $user_stats['badges_earned'] + 1;
                
                $wpdb->update(
                    $wpdb->prefix . 'jph_user_stats',
                    array(
                        'total_xp' => $new_xp,
                        'badges_earned' => $new_badge_count
                    ),
                    array('user_id' => $user_id),
                    array('%d', '%d'),
                    array('%d')
                );
                
                wp_send_json_success(array(
                    'message' => 'First Steps badge awarded successfully!',
                    'new_xp' => $new_xp
                ));
            } else {
                wp_send_json_error('Failed to insert badge: ' . $wpdb->last_error);
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
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
                    <span class="jph-message-close">&times;</span>
                    <div class="jph-message-text"></div>
                </div>
            </div>
            
            <div class="jph-header">
                <h2>🎹 Your Practice Dashboard</h2>
                <div class="jph-stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo esc_html($user_stats['current_level']); ?></span>
                        <span class="stat-label">Level</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo esc_html($user_stats['total_xp']); ?></span>
                        <span class="stat-label">XP</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo esc_html($user_stats['current_streak']); ?></span>
                        <span class="stat-label">Streak</span>
                    </div>
                </div>
                

                
            </div>
            
            <!-- Lesson Favorites Section -->
            <div class="jph-lesson-favorites-section">
                <h3>⭐ My Lesson Favorites</h3>
                <div class="jph-favorites-container" id="lesson-favorites-container">
                    <div class="loading">Loading your lesson favorites...</div>
                </div>
            </div>
            
            <div class="jph-practice-items">
                 <!-- Stats Explanation Button -->
                 <div class="stats-explanation-button">
                    <button id="jph-stats-explanation-btn" type="button" class="jph-btn jph-btn-secondary">
                        <span class="btn-icon">📊</span>
                        How do these stats work?
                    </button>
                </div>
                <h3>Your Practice Items 
                    <span class="item-count">(<?php echo count($practice_items); ?>/3)</span>
                </h3>
                <div class="neuroscience-note">
                    <p>🧠 <strong>Neuroscience Tip:</strong> Limiting to 3 practice items helps your brain focus and improves learning efficiency. Quality over quantity!</p>
                </div>
                <div class="jph-items-grid">
                    <?php 
                    // Always show 3 cards
                    for ($i = 0; $i < 3; $i++): 
                        if (isset($practice_items[$i])):
                            $item = $practice_items[$i];
                    ?>
                        <div class="jph-item" data-item-id="<?php echo esc_attr($item['id']); ?>">
                            <div class="item-info">
                                <h4><?php echo esc_html($item['name']); ?></h4>
                                <p><?php echo esc_html($item['description']); ?></p>
                                <span class="item-category"><?php echo esc_html($item['category']); ?></span>
                            </div>
                            <div class="item-actions">
                                <button class="jph-log-practice-btn" data-item-id="<?php echo esc_attr($item['id']); ?>">
                                    Log Practice
                                </button>
                                <button class="jph-edit-item-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" data-category="<?php echo esc_attr($item['category']); ?>" data-description="<?php echo esc_attr($item['description']); ?>">
                                    Edit
                                </button>
                                <button class="jph-delete-item-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>">
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="jph-item jph-empty-item">
                            <div class="item-info">
                                <h4>Empty Slot</h4>
                                <p>Add a new practice item to get started!</p>
                            </div>
                            <div class="item-actions">
                                <button class="jph-add-item-btn" type="button">
                                    Add Practice Item
                                </button>
                                <button class="jph-add-favorite-btn" type="button" onclick="addLessonFavorite()">
                                    ⭐ Add Lesson Favorite
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3 style="margin: 0;">🏆 Your Badges 
                        <span class="badge-count" id="badge-count-display">(<?php echo esc_html($user_stats['badges_earned']); ?>)</span>
                </h3>
                    <button type="button" class="button button-small" id="refresh-badge-count-btn" style="font-size: 12px; padding: 4px 8px;">🔄 Refresh Count</button>
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
                <div class="jph-modal-content">
                    <span class="jph-close">&times;</span>
                    <h3>🎹 Log Practice Session</h3>
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
                        
                        <button type="submit" class="log-session-btn">🎯 Log Practice Session</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Practice Item Modal -->
        <div id="jph-edit-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <span class="jph-close">&times;</span>
                <h3>Edit Practice Item</h3>
                <form id="jph-edit-form">
                    <input type="hidden" id="edit-item-id" name="item_id">
                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="item_category" required>
                            <option value="">Select category...</option>
                            <option value="technique">Technique</option>
                            <option value="theory">Theory</option>
                            <option value="ear-training">Ear Training</option>
                            <option value="repertoire">Repertoire</option>
                            <option value="rhythm">Rhythm</option>
                            <option value="chords">Chords</option>
                            <option value="improvisation">Improvisation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="item_description" placeholder="Describe what you'll practice (optional)"></textarea>
                    </div>
                    <button type="submit">Update Practice Item</button>
                </form>
            </div>
        </div>
        
        <!-- Add Practice Item Modal -->
        <div id="jph-add-item-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <span class="jph-close">&times;</span>
                <h3>➕ Add Practice Item</h3>
                <form id="jph-add-item-form">
                    <div class="form-group">
                        <label>Choose Practice Type:</label>
                        <div class="practice-type-selection">
                            <label class="radio-option">
                                <input type="radio" name="practice_type" value="custom" checked>
                                <span>Enter your own practice item</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="practice_type" value="favorite">
                                <span>Choose from lesson favorites</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="custom-title-group">
                        <label>Title:</label>
                        <input type="text" name="item_name" placeholder="e.g., Major Scale Practice" required>
                    </div>
                    
                    <div class="form-group" id="favorite-selection-group" style="display: none;">
                        <label>Select Lesson Favorite:</label>
                        <select name="lesson_favorite" id="lesson-favorite-select">
                            <option value="">Loading favorites...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="item_category" required>
                            <option value="">Select category...</option>
                            <option value="technique">Technique</option>
                            <option value="theory">Theory</option>
                            <option value="ear-training">Ear Training</option>
                            <option value="repertoire">Repertoire</option>
                            <option value="rhythm">Rhythm</option>
                            <option value="chords">Chords</option>
                            <option value="improvisation">Improvisation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="item_description" placeholder="Describe what you'll practice (optional)"></textarea>
                    </div>
                    <button type="submit">Add Practice Item</button>
                </form>
            </div>
        </div>
        
        <!-- Stats Explanation Modal -->
        <div id="jph-stats-explanation-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <span class="jph-close">&times;</span>
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
                </div>
                <div class="explanation-tip">
                    <strong>💡 Pro Tip:</strong> Consistent daily practice, even for short sessions, is better than long sessions once in a while!
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
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 30px;
            background: linear-gradient(135deg, #004555 0%, #002A34 100%);
            color: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 69, 85, 0.3);
            position: relative;
            overflow: hidden;
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
        
        /* Stats Explanation Button */
        .stats-explanation-button {
            text-align: center;
            margin-top: 20px;
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
            grid-template-columns: 1fr 1fr 1fr;
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
        
        .explanation-tip {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            padding: 15px 20px;
            border-radius: 12px;
            border-left: 4px solid #f39c12;
            color: #8b4513;
            font-size: 14px;
        }
        
        .explanation-tip strong {
            color: #d35400;
        }
        
        /* Make stats explanation modal wider */
        #jph-stats-explanation-modal .jph-modal-content {
            max-width: 1000px;
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
        
        .jph-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #F04E23, #239B90);
        }
        
        .jph-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 69, 85, 0.15);
            border-color: #239B90;
        }
        
        .item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .item-info h4 {
            margin: 0 0 8px 0;
            font-size: 1.4em;
            font-weight: 700;
            color: #004555;
        }
        
        .item-info p {
            margin: 0 0 8px 0;
            color: #666;
            font-size: 1em;
            min-height: 1.2em;
        }
        
        .item-category {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #f0f0f0;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #666;
            height: 24px;
            line-height: 16px;
            z-index: 1;
        }
        
        .item-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: auto;
        }
        
        .jph-log-practice-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .jph-log-practice-btn:hover {
            background: #45a049;
        }
        
        .jph-edit-item-btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 8px;
        }
        
        .jph-edit-item-btn:hover {
            background: #1976D2;
        }
        
        .jph-delete-item-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
        }
        
        .jph-delete-item-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.4);
        }
        
        .item-count {
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }
        
        .neuroscience-note {
            background: #e8f5e8;
            border: 1px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .neuroscience-note p {
            margin: 0;
            color: #2e7d32;
            font-size: 14px;
        }
        
        .jph-practice-items {
            margin-bottom: 30px;
        }
        
        /* Lesson Favorites Section */
        .jph-lesson-favorites-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .jph-lesson-favorites-section h3 {
            margin: 0 0 20px 0;
            color: #2A3940;
            font-size: 20px;
            font-weight: 700;
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
        
        .practice-type-selection {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .radio-option:hover {
            border-color: #0073aa;
            background-color: #f8f9fa;
        }
        
        .radio-option input[type="radio"] {
            margin: 0;
        }
        
        .radio-option input[type="radio"]:checked + span {
            font-weight: 600;
            color: #0073aa;
        }
        
        .radio-option:has(input[type="radio"]:checked) {
            border-color: #0073aa;
            background-color: #e3f2fd;
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
            padding: 20px;
            box-sizing: border-box;
        }
        
        .jph-modal-content {
            background-color: white;
            margin: 0 auto;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 700px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .jph-close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 28px;
            cursor: pointer;
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
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        
        .duration-btn {
            background: #f0f0f0;
            border: 2px solid #ddd;
            padding: 6px 12px;
            border-radius: 16px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .duration-btn:hover {
            background: #e0e0e0;
            border-color: #bbb;
        }
        
        .duration-btn.active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .duration-custom input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
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
            padding: 18px 12px;
            border: 2px solid #ddd;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .sentiment-option:hover {
            border-color: #bbb;
            background: #f9f9f9;
        }
        
        .sentiment-option.active {
            border-color: #4CAF50;
            background: #e8f5e8;
        }
        
        .sentiment-emoji {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .sentiment-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        /* Improvement Toggle */
        .improvement-toggle {
            margin-top: 10px;
        }
        
        .toggle-slider {
            position: relative;
            display: inline-block;
            width: 120px;
            height: 40px;
            background: #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .toggle-slider:before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .toggle-slider-text {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            font-weight: 500;
            color: #666;
        }
        
        .toggle-slider-text:first-child {
            left: 12px;
        }
        
        .toggle-slider-text:last-child {
            right: 12px;
        }
        
        #improvement-toggle {
            display: none;
        }
        
        #improvement-toggle:checked + .toggle-slider {
            background: #4CAF50;
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
        
        /* Log Session Button */
        .log-session-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 15px 24px;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        .log-session-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .jph-modal {
                padding: 10px;
            }
            
            .jph-modal-content {
                max-width: 100%;
                padding: 20px;
                max-height: calc(100vh - 20px);
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
            top: 20px;
            right: 20px;
            z-index: 1001;
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
        </style>
        
        <script>
        // Wait for jQuery to be available
        function jphInit() {
            if (typeof jQuery === 'undefined') {
                setTimeout(jphInit, 100);
                return;
            }
            
            jQuery(document).ready(function($) {
                console.log('JPH Dashboard initialized');
                
                // Load practice history
                loadPracticeHistory();
                
                // Load more sessions button events
                $(document).on('click', '#load-more-sessions, #load-more-sessions-bottom', function() {
                    loadMoreSessions();
                });
                
                // Export history button event
                $(document).on('click', '#export-history-btn', function() {
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
                $(document).on('click', '#view-all-sessions-btn', function() {
                    viewAllSessions();
                });
                
                // Check and award badges, then load them
                checkAndAwardBadges();
                
                
                // Stats Explanation Modal
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
                console.log('JPH: Test gamification with testGamification()');
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
                
                console.log('JPH: Test direct XP with testDirectXP()');
                window.testDirectXP = testDirectXP;
                
                // Message system
                function showMessage(message, type = 'success') {
                    var $messages = $('#jph-messages');
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
                $(document).on('click', '.jph-message-close', function() {
                    $('#jph-messages').hide();
                });
                
                // Global variables for pagination
                var currentSessions = [];
                var sessionsLoaded = 0;
                var sessionsPerLoad = 50;
                var isLoadingMore = false;
                
                // Load practice history
                function loadPracticeHistory() {
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'GET',
                        data: { limit: sessionsPerLoad },
                        success: function(response) {
                            if (response.success) {
                                currentSessions = response.sessions;
                                sessionsLoaded = currentSessions.length;
                                displayPracticeHistory(currentSessions);
                                
                                // Show load more button if we have sessions and might have more
                                if (sessionsLoaded >= sessionsPerLoad) {
                                    $('#load-more-container').show();
                                }
                            } else {
                                $('#practice-history-list').html('<div class="no-sessions-message"><span class="emoji">📝</span>No practice sessions found</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading practice history:', error);
                            $('#practice-history-list').html('<div class="no-sessions-message"><span class="emoji">❌</span>Error loading practice history</div>');
                        }
                    });
                }
                
                // Load more practice sessions
                function loadMoreSessions() {
                    if (isLoadingMore) return;
                    
                    isLoadingMore = true;
                    $('#load-more-sessions-bottom').html('<span class="btn-icon">⏳</span>Loading...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'GET',
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
                    var $btn = $('#export-history-btn');
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
                    var $btn = $('#view-all-sessions-btn');
                    var $container = $('.practice-history-list');
                    
                    if ($btn.text().includes('View All')) {
                        // Load all sessions
                        $btn.html('<span class="btn-icon">⏳</span>Loading All Sessions...');
                        
                        $.ajax({
                            url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                            method: 'GET',
                            data: { limit: 9999 }, // Get all sessions
                            success: function(response) {
                                if (response.success) {
                                    currentSessions = response.sessions;
                                    sessionsLoaded = currentSessions.length;
                                    displayPracticeHistory(currentSessions);
                                    
                                    // Hide load more button and show collapse option
                                    $('#load-more-container').hide();
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
                                    console.log('New badges awarded:', response.newly_awarded);
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
                
                // Load lesson favorites
                function loadLessonFavorites() {
                    console.log('DEBUG: Loading lesson favorites...');
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/lesson-favorites'); ?>',
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        success: function(response) {
                            console.log('DEBUG: Lesson favorites response:', response);
                            if (response.success) {
                                displayLessonFavorites(response.favorites);
                            } else {
                                console.error('Error loading lesson favorites:', response.message);
                                $('#lesson-favorites-container').html('<div class="jph-favorites-empty"><h4>No lesson favorites found</h4><p>Start adding lessons to your favorites!</p></div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading lesson favorites:', error);
                            $('#lesson-favorites-container').html('<div class="jph-favorites-empty"><h4>Error loading favorites</h4><p>Please try refreshing the page.</p></div>');
                        }
                    });
                }
                
                // Display lesson favorites
                function displayLessonFavorites(favorites) {
                    const container = $('#lesson-favorites-container');
                    
                    if (favorites.length === 0) {
                        container.html('<div class="jph-favorites-empty"><h4>No lesson favorites yet</h4><p>Start adding lessons to your favorites!</p></div>');
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
                                    <a href="${escapeHtml(favorite.url)}" target="_blank" class="jph-favorite-btn jph-favorite-btn-primary">View Lesson</a>
                                    <button onclick="deleteLessonFavorite(${favorite.id})" class="jph-favorite-btn jph-favorite-btn-danger">Remove</button>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.html(html);
                }
                
                // Delete lesson favorite
                function deleteLessonFavorite(favoriteId) {
                    if (!confirm('Are you sure you want to remove this lesson favorite?')) {
                        return;
                    }
                    
                    $.ajax({
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
                    console.log('DEBUG: Loading badges...');
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/user-badges'); ?>',
                        method: 'GET',
                        success: function(response) {
                            console.log('DEBUG: Badge API response:', response);
                            if (response.success) {
                                console.log('DEBUG: Success - badges data:', response.badges);
                                console.log('DEBUG: Earned count:', response.earned_count);
                                console.log('DEBUG: Total count:', response.total_count);
                                if (response.debug) {
                                    console.log('DEBUG: Full debug info:', response.debug);
                                }
                                displayBadges(response.badges);
                            } else {
                                console.log('DEBUG: API returned success=false');
                                $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('DEBUG: Error loading badges:', error);
                            console.error('DEBUG: XHR response:', xhr.responseText);
                            $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">❌</span>Error loading badges</div>');
                        }
                    });
                }
                
                // Display badges
                function displayBadges(badges) {
                    console.log('DEBUG: displayBadges called with:', badges);
                    var $container = $('#jph-badges-grid');
                    console.log('DEBUG: Container element:', $container);
                    
                    if (!badges || badges.length === 0) {
                        console.log('DEBUG: No badges to display');
                        $container.html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available yet. Keep practicing to earn your first badge!</div>');
                        return;
                    }
                    
                    console.log('DEBUG: Processing ' + badges.length + ' badges');
                    var html = '';
                    var earnedCount = 0;
                    badges.forEach(function(badge, index) {
                        console.log('DEBUG: Processing badge ' + index + ':', badge);
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
                    
                    console.log('DEBUG: Earned badges count:', earnedCount);
                    console.log('DEBUG: Generated HTML length:', html.length);
                    $container.html(html);
                    console.log('DEBUG: HTML set to container');
                    
                    // Update the badge count display
                    updateBadgeCount(earnedCount);
                }
                
        // Update badge count display
        function updateBadgeCount(count) {
            var $badgeCount = $('#badge-count-display');
            if ($badgeCount.length) {
                $badgeCount.text('(' + count + ')');
                console.log('DEBUG: Updated badge count to:', count);
            }
        }
        
        // Toast notification system
        function showToast(message, type = 'info', duration = 4000) {
            // Remove existing toasts
            $('.jph-toast').remove();
            
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            
            const toast = $(`
                <div class="jph-toast ${type}">
                    <div class="toast-content">
                        <span class="toast-icon">${icon}</span>
                        <span class="toast-message">${message}</span>
                        <button class="toast-close" onclick="$(this).parent().parent().remove()">×</button>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            
            // Show toast
            setTimeout(() => toast.addClass('show'), 100);
            
            // Auto-hide after duration
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
                
                // Refresh badge count button
                $('#refresh-badge-count-btn').on('click', function() {
                    console.log('DEBUG: Refresh badge count button clicked');
                    loadBadges();
                });
                
                // Get progress text for unearned badges
                
                
                // Display practice history
                function displayPracticeHistory(sessions) {
                    var $container = $('#practice-history-list');
                    
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
                    var currentXP = parseInt($('.stat-value').eq(1).text()) || 0;
                    var newXP = currentXP + (response.xp_earned || 0);
                    $('.stat-value').eq(1).text(newXP);
                    
                    // Update level if leveled up
                    if (response.level_up && response.level_up.leveled_up) {
                        $('.stat-value').eq(0).text(response.level_up.new_level);
                    }
                    
                    // Update streak
                    if (response.streak_update) {
                        $('.stat-value').eq(2).text(response.streak_update.current_streak);
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
                $(document).on('click', '.jph-add-item-btn', function() {
                    $('#jph-add-item-modal').show();
                    loadLessonFavorites();
                });
                
                // Handle practice type radio button changes
                $(document).on('change', 'input[name="practice_type"]', function() {
                    var practiceType = $(this).val();
                    if (practiceType === 'custom') {
                        $('#custom-title-group').show();
                        $('#favorite-selection-group').hide();
                        $('input[name="item_name"]').prop('required', true);
                        $('select[name="lesson_favorite"]').prop('required', false);
                    } else if (practiceType === 'favorite') {
                        $('#custom-title-group').hide();
                        $('#favorite-selection-group').show();
                        $('input[name="item_name"]').prop('required', false);
                        $('select[name="lesson_favorite"]').prop('required', true);
                    }
                });
                
                // Handle lesson favorite selection
                $(document).on('change', '#lesson-favorite-select', function() {
                    var selectedOption = $(this).find('option:selected');
                    if (selectedOption.val()) {
                        var title = selectedOption.data('title');
                        var category = selectedOption.data('category');
                        var description = selectedOption.data('description');
                        
                        // Auto-fill the form fields
                        $('input[name="item_name"]').val(title);
                        $('select[name="item_category"]').val(category);
                        $('textarea[name="item_description"]').val(description);
                    }
                });
                
                // Load lesson favorites
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
                
                // Add lesson favorite function (called by button)
                function addLessonFavorite() {
                    var title = prompt('Enter lesson title:');
                    if (!title) return;
                    
                    var url = prompt('Enter lesson URL:');
                    if (!url) return;
                    
                    var category = prompt('Enter category (optional):') || 'lesson';
                    var description = prompt('Enter description (optional):') || '';
                    
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/lesson-favorites'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            title: title,
                            url: url,
                            category: category,
                            description: description
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage('Lesson favorite added successfully!');
                                loadLessonFavorites(); // Refresh the dropdown
                            } else {
                                showMessage('Error: ' + (response.message || 'Unknown error'), 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = 'Error adding lesson favorite';
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // Use default error message
                            }
                            showMessage(errorMessage, 'error');
                        }
                    });
                }
                
                // Close Add Practice Item modal
                $(document).on('click', '#jph-add-item-modal .jph-close', function() {
                    $('#jph-add-item-modal').hide();
                });
                
                // Close modal when clicking outside
                $(document).on('click', '#jph-add-item-modal', function(e) {
                    if (e.target === this) {
                        $(this).hide();
                    }
                });
                
                // Add practice item
                $('#jph-add-item-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"]');
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Adding...');
                    
                    var formData = {
                        name: $form.find('input[name="item_name"]').val(),
                        category: $form.find('select[name="item_category"]').val(),
                        description: $form.find('textarea[name="item_description"]').val()
                    };
                    
                    // Validate form data
                    if (!formData.name) {
                        showMessage('Please enter a practice item name', 'error');
                        $form.removeClass('jph-loading');
                        $button.prop('disabled', false).text('Add Practice Item');
                        return;
                    }
                    
                    if (!formData.category) {
                        showMessage('Please select a category', 'error');
                        $form.removeClass('jph-loading');
                        $button.prop('disabled', false).text('Add Practice Item');
                        return;
                    }
                    
                    console.log('Sending data:', formData);
                    
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-items'); ?>',
                        method: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        success: function(response) {
                            if (response.success) {
                                showMessage('Practice item added successfully!');
                                $form[0].reset(); // Clear form
                                $('#jph-add-item-modal').hide(); // Close modal
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
                    
                    var itemHtml = '<div class="jph-item" data-item-id="' + itemId + '">' +
                        '<div class="item-info">' +
                        '<h4>' + name + '</h4>' +
                        '<p>' + (description || '') + '</p>' +
                        '<span class="item-category">' + category + '</span>' +
                        '</div>' +
                        '<div class="item-actions">' +
                        '<button class="jph-log-practice-btn" data-item-id="' + itemId + '">Log Practice</button>' +
                        '<button class="jph-edit-item-btn" data-item-id="' + itemId + '" data-name="' + name + '" data-category="' + category + '" data-description="' + (description || '') + '">Edit</button>' +
                        '<button class="jph-delete-item-btn" data-item-id="' + itemId + '" data-name="' + name + '">Delete</button>' +
                        '</div>' +
                        '</div>';
                    
                    $('.jph-items-list').append(itemHtml);
                }
                
                // Open log practice modal
                $(document).on('click', '.jph-log-practice-btn', function() {
                    var itemId = $(this).data('item-id');
                    $('#log-item-id').val(itemId);
                    $('#jph-log-modal').show();
                });
                
                // Close modal
                $(document).on('click', '.jph-close', function() {
                    $('#jph-log-modal').hide();
                    $('#jph-edit-modal').hide();
                });
                
                // Edit practice item
                $(document).on('click', '.jph-edit-item-btn', function() {
                    var itemId = $(this).data('item-id');
                    var name = $(this).data('name');
                    var category = $(this).data('category');
                    var description = $(this).data('description');
                    
                    console.log('Edit button clicked - Data:', {itemId, name, category, description});
                    
                    $('#edit-item-id').val(itemId);
                    $('#jph-edit-form input[name="item_name"]').val(name);
                    $('#jph-edit-form select[name="item_category"]').val(category);
                    $('#jph-edit-form textarea[name="item_description"]').val(description);
                    $('#jph-edit-modal').show();
                });
                
                // Delete practice item
                $(document).on('click', '.jph-delete-item-btn', function() {
                    var itemId = $(this).data('item-id');
                    var name = $(this).data('name');
                    
                    if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
                        $.ajax({
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
                            success: function(response) {
                                if (response.success) {
                                    showMessage('Practice session deleted successfully!');
                                    $('.practice-session').filter(function() {
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
                $('#jph-edit-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"]');
                    var itemId = $('#edit-item-id').val();
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Updating...');
                    
                    var formData = {
                        name: $form.find('input[name="item_name"]').val(),
                        category: $form.find('select[name="item_category"]').val(),
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
                    
                    if (!formData.category) {
                        showMessage('Please select a category', 'error');
                        $form.removeClass('jph-loading');
                        $button.prop('disabled', false).text('Update Practice Item');
                        return;
                    }
                    
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-items/'); ?>' + itemId,
                        method: 'PUT',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        success: function(response) {
                            if (response.success) {
                                showMessage('Practice item updated successfully!');
                                $('#jph-edit-modal').hide();
                                $form[0].reset();
                                // Update the item in the list
                                updateItemInList(itemId, formData.name, formData.category, formData.description);
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
                    var count = $('.jph-item').length;
                    $('.item-count').text('(' + count + '/3)');
                }
                
                // Duration quick buttons
                $(document).on('click', '.duration-btn', function() {
                    $('.duration-btn').removeClass('active');
                    $(this).addClass('active');
                    $('input[name="duration_minutes"]').val($(this).data('minutes'));
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
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'POST',
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
                                $('#jph-log-modal').hide();
                                $form[0].reset();
                                // Reset UI elements
                                $('.duration-btn').removeClass('active');
                                $('.sentiment-option').removeClass('active');
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
                            $button.prop('disabled', false).text('🎯 Log Practice Session');
                        }
                    });
                });
            });
        }
        
        // Start initialization
        jphInit();
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
        // Create database tables (coming soon)
        error_log('JPH: Plugin activated');
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
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup (coming soon)
        error_log('JPH: Plugin deactivated');
    }
}

// Initialize the plugin
function jazzedge_practice_hub() {
    return JazzEdge_Practice_Hub::get_instance();
}

// Start the plugin
jazzedge_practice_hub();
