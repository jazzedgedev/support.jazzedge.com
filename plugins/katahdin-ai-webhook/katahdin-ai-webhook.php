<?php
/**
 * Plugin Name: Katahdin AI Webhook
 * Plugin URI: https://katahdin.ai
 * Description: AI-powered webhook processor that analyzes FluentForm submissions using OpenAI and sends results via email.
 * Version: 1.0.0
 * Author: Katahdin AI - katahdin.ai
 * Author URI: https://katahdin.ai
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: katahdin-ai-webhook
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
if (!defined('KATAHDIN_AI_WEBHOOK_VERSION')) {
    define('KATAHDIN_AI_WEBHOOK_VERSION', '1.0.0');
}
if (!defined('KATAHDIN_AI_WEBHOOK_PLUGIN_URL')) {
    define('KATAHDIN_AI_WEBHOOK_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('KATAHDIN_AI_WEBHOOK_PLUGIN_PATH')) {
    define('KATAHDIN_AI_WEBHOOK_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('KATAHDIN_AI_WEBHOOK_PLUGIN_BASENAME')) {
    define('KATAHDIN_AI_WEBHOOK_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Check if Katahdin AI Hub is active
if (!function_exists('katahdin_ai_hub')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>Katahdin AI Webhook</strong> requires <strong>Katahdin AI Hub</strong> to be installed and activated.</p></div>';
    });
    // Don't return early - let the plugin load for debugging
    error_log('Katahdin AI Webhook: Katahdin AI Hub not available, but continuing to load for debugging');
}

// Include required files
$includes_path = KATAHDIN_AI_WEBHOOK_PLUGIN_PATH . 'includes/';

if (file_exists($includes_path . 'class-webhook-logger.php')) {
    require_once $includes_path . 'class-webhook-logger.php';
}

if (file_exists($includes_path . 'class-webhook-handler.php')) {
    require_once $includes_path . 'class-webhook-handler.php';
}
if (file_exists($includes_path . 'class-admin.php')) {
    require_once $includes_path . 'class-admin.php';
}
if (file_exists($includes_path . 'class-plugin-debugger.php')) {
    require_once $includes_path . 'class-plugin-debugger.php';
}
if (file_exists($includes_path . 'class-email-sender.php')) {
    require_once $includes_path . 'class-email-sender.php';
}

/**
 * Main Katahdin AI Webhook Plugin Class
 */
if (!class_exists('Katahdin_AI_Webhook')) {
class Katahdin_AI_Webhook {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Plugin ID for Katahdin AI Hub
     */
    const PLUGIN_ID = 'katahdin-ai-webhook';
    
    /**
     * Webhook Handler
     */
    public $webhook_handler;
    
    /**
     * Admin Interface
     */
    public $admin;
    
    /**
     * Email Sender
     */
    public $email_sender;
    
    /**
     * Plugin Debugger
     */
    public $debugger;
    
    /**
     * Get single instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'init_rest_api'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Register with Katahdin AI Hub
        add_action('katahdin_ai_hub_init', array($this, 'register_with_hub'));
        
        // Also try to register immediately if hub is available
        add_action('init', array($this, 'try_register_with_hub'), 20);
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        try {
            if (class_exists('Katahdin_AI_Webhook_Handler')) {
                $this->webhook_handler = new Katahdin_AI_Webhook_Handler();
            }
            if (class_exists('Katahdin_AI_Webhook_Admin')) {
                $this->admin = new Katahdin_AI_Webhook_Admin();
            }
            if (class_exists('Katahdin_AI_Webhook_Email_Sender')) {
                $this->email_sender = new Katahdin_AI_Webhook_Email_Sender();
            }
            if (class_exists('Katahdin_AI_Plugin_Debugger')) {
                $this->debugger = new Katahdin_AI_Plugin_Debugger(self::PLUGIN_ID, 'Katahdin AI Webhook');
            }
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook component initialization error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('katahdin-ai-webhook', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components safely
        try {
            if ($this->webhook_handler && method_exists($this->webhook_handler, 'init')) {
                $this->webhook_handler->init();
            }
            if ($this->admin && method_exists($this->admin, 'init')) {
                $this->admin->init();
            }
            if ($this->email_sender && method_exists($this->email_sender, 'init')) {
                $this->email_sender->init();
            }
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook initialization error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize REST API
     */
    public function init_rest_api() {
        // Register a simple test route directly
        register_rest_route('katahdin-ai-webhook/v1', '/test-plugin', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_plugin_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        if ($this->webhook_handler && method_exists($this->webhook_handler, 'init_rest_api')) {
            $this->webhook_handler->init_rest_api();
        }
    }
    
    /**
     * Test plugin endpoint
     */
    public function test_plugin_endpoint($request) {
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Katahdin AI Webhook plugin is working',
            'plugin_version' => KATAHDIN_AI_WEBHOOK_VERSION,
            'webhook_handler_available' => $this->webhook_handler ? true : false,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'katahdin-ai-webhook') !== false) {
            wp_enqueue_script('katahdin-ai-webhook-admin', KATAHDIN_AI_WEBHOOK_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), KATAHDIN_AI_WEBHOOK_VERSION, true);
            wp_enqueue_style('katahdin-ai-webhook-admin', KATAHDIN_AI_WEBHOOK_PLUGIN_URL . 'assets/css/admin.css', array(), KATAHDIN_AI_WEBHOOK_VERSION);
            
            wp_localize_script('katahdin-ai-webhook-admin', 'katahdin_ai_webhook', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('katahdin-ai-webhook/v1/'),
                'nonce' => wp_create_nonce('katahdin_ai_webhook_nonce'),
                'strings' => array(
                    'test_webhook_success' => __('Webhook test successful!', 'katahdin-ai-webhook'),
                    'test_webhook_failed' => __('Webhook test failed.', 'katahdin-ai-webhook'),
                    'settings_saved' => __('Settings saved successfully!', 'katahdin-ai-webhook'),
                    'error_occurred' => __('An error occurred. Please try again.', 'katahdin-ai-webhook'),
                )
            ));
        }
    }
    
    /**
     * Try to register with Katahdin AI Hub immediately
     */
    public function try_register_with_hub() {
        if (function_exists('katahdin_ai_hub')) {
            $hub = katahdin_ai_hub();
            if ($hub && isset($hub->plugin_registry)) {
                $this->register_with_hub($hub);
            }
        }
    }
    
    /**
     * Register with Katahdin AI Hub
     */
    public function register_with_hub($hub = null) {
        if (!$hub && function_exists('katahdin_ai_hub')) {
            $hub = katahdin_ai_hub();
        }
        
        if ($hub && isset($hub->plugin_registry)) {
            // Check if the plugin registry has a register method
            if (method_exists($hub->plugin_registry, 'register')) {
                $config = array(
                    'name' => 'Katahdin AI Webhook',
                    'version' => KATAHDIN_AI_WEBHOOK_VERSION,
                    'features' => array(
                        'webhook_processing',
                        'ai_analysis',
                        'email_notifications'
                    ),
                    'quota_limit' => 5000 // Monthly token limit
                );
                
                $result = $hub->plugin_registry->register(self::PLUGIN_ID, $config);
                
                // Only log errors, not successful registrations
                if (is_wp_error($result)) {
                    error_log('Katahdin AI Webhook registration failed: ' . $result->get_error_message());
                }
            } else {
                error_log('Katahdin AI Webhook: Plugin registry does not have register method');
            }
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        add_option('katahdin_ai_webhook_prompt', 'Analyze the following form submission data and provide insights, recommendations, or summaries as appropriate. Be concise but informative.');
        add_option('katahdin_ai_webhook_email', get_option('admin_email'));
        add_option('katahdin_ai_webhook_email_subject', 'AI Analysis Results - Form Submission');
        add_option('katahdin_ai_webhook_webhook_secret', wp_generate_password(32, false));
        add_option('katahdin_ai_webhook_enabled', true);
        add_option('katahdin_ai_webhook_model', 'gpt-3.5-turbo');
        add_option('katahdin_ai_webhook_max_tokens', 1000);
        add_option('katahdin_ai_webhook_temperature', 0.7);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Get webhook URL
     */
    public function get_webhook_url() {
        return rest_url('katahdin-ai-webhook/v1/webhook');
    }
    
    /**
     * Get webhook secret
     */
    public function get_webhook_secret() {
        return get_option('katahdin_ai_webhook_webhook_secret', '');
    }
    
    /**
     * Process webhook data
     */
    public function process_webhook_data($data) {
        if (!$this->webhook_handler) {
            return new WP_Error('handler_not_available', 'Webhook handler not available');
        }
        
        return $this->webhook_handler->process_data($data);
    }
}
}

/**
 * Global function to access the webhook instance
 */
if (!function_exists('katahdin_ai_webhook')) {
function katahdin_ai_webhook() {
    return Katahdin_AI_Webhook::instance();
}
}

// Plugin activation hook
register_activation_hook(__FILE__, 'katahdin_ai_webhook_activate');

function katahdin_ai_webhook_activate() {
    // Create webhook logs table
    if (class_exists('Katahdin_AI_Webhook_Logger')) {
        $logger = new Katahdin_AI_Webhook_Logger();
        $logger->create_table();
    }
    
    // Set default options
    add_option('katahdin_ai_webhook_enabled', true);
    add_option('katahdin_ai_webhook_log_retention_days', 30);
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'katahdin_ai_webhook_deactivate');

function katahdin_ai_webhook_deactivate() {
    // Clean up any scheduled events if needed
}

// Initialize the plugin
if (!function_exists('katahdin_ai_webhook_init')) {
function katahdin_ai_webhook_init() {
    // Always initialize the plugin, even if hub is not available
    return Katahdin_AI_Webhook::instance();
}
}

// Start the plugin
if (!function_exists('katahdin_ai_webhook_started')) {
    function katahdin_ai_webhook_started() {
        return true;
    }
    katahdin_ai_webhook_init();
}

// Ensure plugin is initialized on WordPress init
add_action('init', 'katahdin_ai_webhook_init', 5);
