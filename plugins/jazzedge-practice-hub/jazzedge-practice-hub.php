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
                </div>
                <div id="jph-database-results" class="jph-database-results"></div>
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
            </div>
            
            <div class="jph-students-table-container">
                <table class="jph-students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Level</th>
                            <th>XP</th>
                            <th>Streak</th>
                            <th>Last Practice</th>
                            <th>Total Sessions</th>
                            <th>Total Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <tr>
                            <td colspan="8" class="jph-loading">Loading students...</td>
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
        function loadStudentsData() {
            const tbody = document.getElementById('students-table-body');
            tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Loading students...</td></tr>';
            
            fetch('<?php echo rest_url('jph/v1/students'); ?>', {
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
                    tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Error loading students</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">Error loading students</td></tr>';
            });
        }
        
        // Render students table
        function renderStudentsTable(students) {
            const tbody = document.getElementById('students-table-body');
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="jph-loading">No students found</td></tr>';
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
                    <td><span class="jph-last-practice">${formatDate(student.stats.last_practice_date)}</span></td>
                    <td><span class="jph-sessions-count">${student.stats.total_sessions}</span></td>
                    <td><span class="jph-hours-display">${Math.round(student.stats.total_minutes / 60 * 10) / 10}h</span></td>
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
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays === 1) return 'Yesterday';
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
                            <label>Gems</label>
                            <div class="value">${student.stats.gems_balance}</div>
                        </div>
                        <div class="jph-student-detail-item">
                            <label>Badges Earned</label>
                            <div class="value">${student.stats.badges_earned}</div>
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
                    alert('Student stats updated successfully!');
                } else {
                    alert('Error updating student stats: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error updating student stats:', error);
                alert('Error updating student stats');
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
            alert('Export students to CSV - Coming Soon');
        }
        
        function showStudentAnalytics() {
            alert('Student analytics - Coming Soon');
        }
        </script>
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
            $user_id = $request->get_param('user_id') ?: 1; // Default to user 1 for testing
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
            $user_id = $request->get_param('user_id') ?: 1; // Default to user 1 for testing
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
            
            // Update total minutes in stats
            $database->update_user_stats($user_id, array('total_minutes' => $duration_minutes));
            
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
            $limit = $request->get_param('limit') ?: 10; // Default to 10 recent sessions
            
            $sessions = $database->get_practice_sessions($user_id, $limit);
            
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
            $user_id = $request->get_param('user_id') ?: 1; // Default to user 1 for testing
            
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
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('no_user', 'No user logged in', array('status' => 401));
            }
            
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
            
            // Get all users who have practice stats
            $table_name = $wpdb->prefix . 'jph_user_stats';
            $users_table = $wpdb->prefix . 'users';
            
            $query = "
                SELECT u.ID, u.user_email, u.display_name, s.*
                FROM {$users_table} u
                LEFT JOIN {$table_name} s ON u.ID = s.user_id
                WHERE s.user_id IS NOT NULL
                ORDER BY s.total_xp DESC, u.display_name ASC
            ";
            
            $results = $wpdb->get_results($query);
            
            $students = array();
            foreach ($results as $row) {
                $students[] = array(
                    'ID' => (int) $row->ID,
                    'user_email' => $row->user_email,
                    'display_name' => $row->display_name ?: $row->user_email,
                    'stats' => array(
                        'total_xp' => (int) $row->total_xp,
                        'current_level' => (int) $row->current_level,
                        'current_streak' => (int) $row->current_streak,
                        'longest_streak' => (int) $row->longest_streak,
                        'total_sessions' => (int) $row->total_sessions,
                        'total_minutes' => (int) $row->total_minutes,
                        'badges_earned' => (int) $row->badges_earned,
                        'hearts_count' => (int) $row->hearts_count,
                        'gems_balance' => (int) $row->gems_balance,
                        'last_practice_date' => $row->last_practice_date
                    )
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
            
            $student = array(
                'ID' => (int) $result->ID,
                'user_email' => $result->user_email,
                'display_name' => $result->display_name ?: $result->user_email,
                'stats' => array(
                    'total_xp' => (int) $result->total_xp,
                    'current_level' => (int) $result->current_level,
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
                <div class="jph-items-list">
                    <?php if (empty($practice_items)): ?>
                        <div class="jph-empty-state">
                            <p>No practice items yet. Add your first practice item below!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($practice_items as $item): ?>
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
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="jph-two-column-layout">
                <!-- Left Column: Practice History -->
                <div class="jph-left-column">
                    <div class="jph-practice-history">
                        <h3>📊 Recent Practice Sessions</h3>
                        <div class="practice-history-list" id="practice-history-list">
                            <div class="loading-message">Loading practice history...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Add Item -->
                <div class="jph-right-column">
                    <div class="jph-add-item">
                <h3>Add Practice Item</h3>
                <form id="jph-add-item-form">
                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" name="item_name" placeholder="e.g., Major Scale Practice" required>
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
        
        .jph-items-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
                                url: '<?php echo rest_url('jph/v1/user-stats'); ?>?user_id=1',
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
                                    url: '<?php echo rest_url('jph/v1/user-stats'); ?>?user_id=1',
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
                
                // Load practice history
                function loadPracticeHistory() {
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'GET',
                        data: { limit: 10 },
                        success: function(response) {
                            if (response.success) {
                                displayPracticeHistory(response.sessions);
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
                        
                        html += '<div class="practice-session">';
                        html += '<div class="session-header">';
                        html += '<div class="session-item-name">' + escapeHtml(session.item_name || 'Unknown Item') + '</div>';
                        html += '<div class="session-date">' + formattedDate + ' <button class="jph-delete-session-btn" data-session-id="' + session.id + '" data-item-name="' + escapeHtml(session.item_name || 'Unknown Item') + '" title="Delete this practice session">🗑️</button></div>';
                        html += '</div>';
                        html += '<div class="session-details">';
                        html += '<div class="session-detail session-duration">';
                        html += '<span class="session-detail-icon">⏱️</span>';
                        html += session.duration_minutes + ' min';
                        html += '</div>';
                        html += '<div class="session-detail">';
                        html += '<span class="session-sentiment">' + sentimentEmoji + '</span>';
                        html += '</div>';
                        html += '<div class="session-detail ' + improvementClass + '">';
                        html += '<span class="session-detail-icon">📈</span>';
                        html += improvementText;
                        html += '</div>';
                        html += '</div>';
                        if (session.notes) {
                            html += '<div class="session-notes">' + escapeHtml(session.notes) + '</div>';
                        }
                        html += '</div>';
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
                    var date = new Date(dateString);
                    var now = new Date();
                    var diffTime = Math.abs(now - date);
                    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays === 1) {
                        return 'Today';
                    } else if (diffDays === 2) {
                        return 'Yesterday';
                    } else if (diffDays <= 7) {
                        return diffDays - 1 + ' days ago';
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
                
                // Add practice item
                $('#jph-add-item-form').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"]');
                    
                    // Disable form during submission
                    $form.addClass('jph-loading');
                    $button.prop('disabled', true).text('Adding...');
                    
                    var formData = {
                        name: $('input[name="item_name"]').val(),
                        category: $('select[name="item_category"]').val(),
                        description: $('textarea[name="item_description"]').val()
                    };
                    
                    // Validate form data
                    if (!formData.name) {
                        showMessage('Please enter a practice item name', 'error');
                        return;
                    }
                    
                    if (!formData.category) {
                        showMessage('Please select a category', 'error');
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
                                // Add item to list without page refresh
                                addItemToList(response.item_id, formData.name, formData.category, formData.description);
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
