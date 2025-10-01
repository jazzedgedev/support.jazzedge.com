<?php
/**
 * Plugin Name: Fluent Support AI Integration
 * Plugin URI: https://katahdin.ai/
 * Description: Integrate OpenAI AI capabilities into Fluent Support ticket system for automated reply generation. Powered by Katahdin AI.
 * Version: 1.0.1
 * Author: Katahdin AI
 * Author URI: https://katahdin.ai/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fluent-support-ai
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FLUENT_SUPPORT_AI_VERSION', '1.0.1');
define('FLUENT_SUPPORT_AI_PLUGIN_FILE', __FILE__);
define('FLUENT_SUPPORT_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FLUENT_SUPPORT_AI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FLUENT_SUPPORT_AI_PLUGIN_BASENAME', plugin_basename(__FILE__));

// JazzEdge API constants
define('JAZZEDGE_MAIN_SITE_URL', 'https://jazzedge.com');
define('JAZZEDGE_API_KEY', 'je_api_2024_K9m7nQ8vL3xR6tY2wE9rP5sA1dF4hJ7k');

/**
 * Main plugin class
 */
class FluentSupportAI {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
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
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
        add_action('admin_init', array($this, 'handle_form_submission'));
        add_action('admin_init', array($this, 'handle_ticket_viewer'));
        add_action('admin_init', array($this, 'handle_ai_generator'));
        add_action('admin_init', array($this, 'handle_quick_links'));
        // AJAX handler for AI generation
        add_action('wp_ajax_fluent_support_ai_generate_response', array($this, 'generate_ai_response'));
        // AJAX handler for saving prompts
        add_action('wp_ajax_fluent_support_ai_save_prompt', array($this, 'save_prompt_ajax'));
        // AJAX handler for deleting prompts
        add_action('wp_ajax_fluent_support_ai_delete_prompt', array($this, 'delete_prompt_ajax'));
        // AJAX handler for quick links
        add_action('wp_ajax_fluent_support_ai_save_quick_link', array($this, 'save_quick_link_ajax'));
        add_action('wp_ajax_fluent_support_ai_delete_quick_link', array($this, 'delete_quick_link_ajax'));
        add_action('wp_ajax_fluent_support_ai_update_quick_link', array($this, 'update_quick_link_ajax'));
        add_action('wp_ajax_fluent_support_ai_reorder_quick_links', array($this, 'reorder_quick_links_ajax'));
        
        // Add AI interface to Fluent Support widgets
        add_filter('fluent_support/customer_extra_widgets', array($this, 'add_ai_widgets'), 50, 2);
        
        // Add admin bar menu for quick links (higher priority to appear on right side)
        add_action('admin_bar_menu', array($this, 'add_quick_links_admin_bar'), 999);
        
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Debug hook to check if admin menu is working
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('admin_notices', array($this, 'debug_admin_notice'));
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('fluent-support-ai', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        require_once FLUENT_SUPPORT_AI_PLUGIN_DIR . 'includes/class-settings.php';
        require_once FLUENT_SUPPORT_AI_PLUGIN_DIR . 'includes/class-prompt-manager.php';
        require_once FLUENT_SUPPORT_AI_PLUGIN_DIR . 'includes/class-openai-client.php';
        require_once FLUENT_SUPPORT_AI_PLUGIN_DIR . 'includes/class-ai-reply-generator.php';
        require_once FLUENT_SUPPORT_AI_PLUGIN_DIR . 'includes/class-webhook-handler.php';
        
        // Include test class only in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once FLUENT_SUPPORT_AI_PLUGIN_DIR . 'includes/class-test.php';
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Create a standalone menu that will always work
        add_menu_page(
            __('Fluent Support AI', 'fluent-support-ai'),
            __('Fluent Support AI', 'fluent-support-ai'),
            'manage_options',
            'fluent-support-ai-settings',
            array($this, 'settings_page'),
            'dashicons-star-filled',
            30
        );
        
        // Add ticket viewer page (hidden from menu)
        add_submenu_page(
            null, // No parent menu
            'AI Ticket Viewer',
            'AI Ticket Viewer',
            'manage_options',
            'fluent-support-ai-ticket-viewer',
            array($this, 'ticket_viewer_page')
        );
        
        // Add AI generator page (hidden from menu)
        add_submenu_page(
            null, // No parent menu
            'AI Generator',
            'AI Generator',
            'manage_options',
            'fluent-support-ai-generator',
            array($this, 'ai_generator_page')
        );
        
        // Add Quick Links page
        add_submenu_page(
            'fluent-support-ai-settings',
            'Quick Links',
            'Quick Links',
            'manage_options',
            'fluent-support-ai-quick-links',
            array($this, 'quick_links_page')
        );
        
        // Also try to add under Fluent Support if it exists
        add_action('admin_menu', array($this, 'add_fluent_support_submenu'), 20);
    }
    
    /**
     * Add submenu under Fluent Support if it exists
     */
    public function add_fluent_support_submenu() {
        // Check if Fluent Support is active and has admin menu
        if (class_exists('FluentSupport') || function_exists('fluentSupport')) {
            add_submenu_page(
                'fluent-support',
                __('AI Integration Settings', 'fluent-support-ai'),
                __('AI Integration', 'fluent-support-ai'),
                'manage_options',
                'fluent-support-ai-settings',
                array($this, 'settings_page')
            );
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'fluent-support-ai') !== false) {
            wp_enqueue_script('fluent-support-ai-admin', FLUENT_SUPPORT_AI_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), FLUENT_SUPPORT_AI_VERSION, true);
            wp_enqueue_style('fluent-support-ai-admin', FLUENT_SUPPORT_AI_PLUGIN_URL . 'assets/css/admin.css', array(), FLUENT_SUPPORT_AI_VERSION);
            
            wp_localize_script('fluent-support-ai-admin', 'fluentSupportAI', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fluent_support_ai_nonce'),
                'strings' => array(
                    'testing' => __('Testing API...', 'fluent-support-ai'),
                    'saving' => __('Saving API key...', 'fluent-support-ai'),
                    'success' => __('API key is valid!', 'fluent-support-ai'),
                    'saved' => __('API key saved successfully!', 'fluent-support-ai'),
                    'error' => __('API key test failed', 'fluent-support-ai'),
                    'generating' => __('Generating reply...', 'fluent-support-ai'),
                    'generated' => __('Reply generated successfully!', 'fluent-support-ai'),
                    'generation_failed' => __('Failed to generate reply', 'fluent-support-ai'),
                )
            ));
        }
        
        // Enqueue scripts for ticket pages
        if (isset($_GET['page']) && (strpos($_GET['page'], 'fluent-support') !== false || strpos($hook, 'fluent-support') !== false)) {
            wp_enqueue_script('fluent-support-ai-ticket', FLUENT_SUPPORT_AI_PLUGIN_URL . 'assets/js/ticket.js', array('jquery'), FLUENT_SUPPORT_AI_VERSION, true);
            wp_enqueue_style('fluent-support-ai-ticket', FLUENT_SUPPORT_AI_PLUGIN_URL . 'assets/css/ticket.css', array(), FLUENT_SUPPORT_AI_VERSION);
            
            wp_localize_script('fluent-support-ai-ticket', 'fluentSupportAI', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fluent_support_ai_nonce'),
                'strings' => array(
                    'generating' => __('Generating AI reply...', 'fluent-support-ai'),
                    'generated' => __('AI reply generated!', 'fluent-support-ai'),
                    'error' => __('Failed to generate reply', 'fluent-support-ai'),
                    'selectPrompt' => __('Please select a prompt', 'fluent-support-ai'),
                )
            ));
        }
    }
    
    /**
     * Enqueue admin bar styles globally
     */
    public function enqueue_admin_bar_styles() {
        if (is_admin_bar_showing()) {
            wp_enqueue_style('fluent-support-ai-admin-bar', FLUENT_SUPPORT_AI_PLUGIN_URL . 'assets/css/admin.css', array(), FLUENT_SUPPORT_AI_VERSION);
        }
    }
    
    /**
     * Settings page callback
     */
    public function settings_page() {
        $settings = new FluentSupportAI_Settings();
        $settings->render();
    }
    
    /**
     * Add AI widgets to Fluent Support
     */
    public function add_ai_widgets($widgets, $customer) {
        // Get prompts from database
        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        $prompts = $prompt_manager->get_prompts();
        
        if (empty($prompts)) {
            return $widgets;
        }
        
        // Get current ticket data
        $ticket_data = $this->get_current_ticket_data();
        
        // Add AI Reply widget
        $widgets['ai_reply'] = [
            'header' => '🤖 AI Reply Generator',
            'body_html' => $this->build_ai_widget($prompts, $ticket_data)
        ];
        
        // Add Customer Tools widget (membership info, autologin, etc.)
        $widgets['customer_tools'] = [
            'header' => '🔍 Customer Tools',
            'body_html' => $this->build_customer_tools_widget($ticket_data)
        ];
        
        return $widgets;
    }
    
    /**
     * Get current ticket data from URL
     */
    private function get_current_ticket_data() {
        $ticket_id = 0;
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        
        // Try to extract ticket ID from URL
        if (preg_match('/tickets\/(\d+)/', $current_url, $matches)) {
            $ticket_id = intval($matches[1]);
        }
        
        if (!$ticket_id) {
            return null;
        }
        
        // Get ticket data from database
        global $wpdb;
        $ticket_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
            $ticket_id
        ), ARRAY_A);
        
        if ($ticket_data) {
        // Get the most recent non-agent content using UNION approach
        $most_recent_content = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                ticket_id,
                title,
                content,
                created_at,
                customer_id,
                agent_id,
                conversation_id,
                person_id,
                content_type
            FROM (
                -- Original ticket content
                SELECT 
                    t.id as ticket_id,
                    t.title,
                    t.content as content,
                    t.created_at as created_at,
                    t.customer_id,
                    t.agent_id,
                    NULL as conversation_id,
                    NULL as person_id,
                    'original_ticket' as content_type
                FROM {$wpdb->prefix}fs_tickets t
                WHERE t.id = %d
                
                UNION ALL
                
                -- All conversations
                SELECT 
                    t.id as ticket_id,
                    t.title,
                    c.content as content,
                    c.created_at as created_at,
                    t.customer_id,
                    t.agent_id,
                    c.id as conversation_id,
                    c.person_id,
                    'conversation' as content_type
                FROM {$wpdb->prefix}fs_tickets t
                INNER JOIN {$wpdb->prefix}fs_conversations c ON t.id = c.ticket_id
                WHERE t.id = %d
            ) combined
            WHERE 
                -- Include original ticket (person_id is NULL)
                person_id IS NULL 
                OR 
                -- Include conversations that are NOT from agents
                person_id NOT IN (
                    SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'agent'
                )
            ORDER BY created_at DESC
            LIMIT 1",
            $ticket_id,
            $ticket_id
        ), ARRAY_A);
        
        // Get all conversations for display (keeping existing logic)
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, p.person_type 
             FROM {$wpdb->prefix}fs_conversations c 
             LEFT JOIN {$wpdb->prefix}fs_persons p ON c.person_id = p.id 
             WHERE c.ticket_id = %d 
             ORDER BY c.created_at ASC",
            $ticket_id
        ), ARRAY_A);
            
            $ticket_data['conversations'] = $conversations;
            
        // Get customer name and email from wp_fs_persons
        $customer_name = 'Customer';
        $customer_email = '';
        if (isset($ticket_data['customer_id'])) {
            $customer = $wpdb->get_row($wpdb->prepare(
                "SELECT first_name, email FROM {$wpdb->prefix}fs_persons WHERE id = %d",
                $ticket_data['customer_id']
            ), ARRAY_A);
            
            if ($customer) {
                if (!empty($customer['first_name'])) {
                    $customer_name = $customer['first_name'];
                }
                if (!empty($customer['email'])) {
                    $customer_email = $customer['email'];
                }
            }
        }
        
        // Add customer email to ticket data
        $ticket_data['customer_email'] = $customer_email;
            
            // Get agent name from wp_fs_persons
            $agent_name = 'Support Agent';
            if (isset($ticket_data['agent_id'])) {
                $agent = $wpdb->get_row($wpdb->prepare(
                    "SELECT first_name FROM {$wpdb->prefix}fs_persons WHERE id = %d",
                    $ticket_data['agent_id']
                ), ARRAY_A);
                
                if ($agent && !empty($agent['first_name'])) {
                    $agent_name = $agent['first_name'];
                }
            }
            
            // Build conversation text - use the most recent non-agent content
            $full_conversation = '';
            
            // Get business context
            $business_name = get_option('fluent_support_ai_business_name', '');
            $business_website = get_option('fluent_support_ai_business_website', '');
            $business_industry = get_option('fluent_support_ai_business_industry', '');
            $business_description = get_option('fluent_support_ai_business_description', '');
            $support_tone = get_option('fluent_support_ai_support_tone', 'professional');
            $support_style = get_option('fluent_support_ai_support_style', '');
            
            // Build business context string
            $business_context = '';
            if ($business_name) {
                $business_context .= "Business: $business_name";
            }
            if ($business_industry) {
                $business_context .= ($business_context ? ', ' : '') . "Industry: $business_industry";
            }
            if ($business_website) {
                $business_context .= ($business_context ? ', ' : '') . "Website: $business_website";
            }
            if ($business_description) {
                $business_context .= ($business_context ? ', ' : '') . "Description: $business_description";
            }
            if ($support_tone) {
                $business_context .= ($business_context ? ', ' : '') . "Tone: $support_tone";
            }
            if ($support_style) {
                $business_context .= ($business_context ? ', ' : '') . "Style: $support_style";
            }
            
            if ($most_recent_content) {
                $content = strip_tags($most_recent_content['content']);
                $full_conversation = "($business_context, Customer first name: $customer_name, Agent first name: $agent_name) $content";
            } else {
                // Fallback to original ticket content if no non-agent content found
                $original_content = strip_tags($ticket_data['content']);
                $full_conversation = "($business_context, Customer first name: $customer_name, Agent first name: $agent_name) $original_content";
            }
            
            $ticket_data['full_conversation_text'] = $full_conversation;
        }
        
        return $ticket_data;
    }
    
    /**
     * Build AI widget HTML
     */
    private function build_ai_widget($prompts, $ticket_data = null) {
        $html = '<div id="fluent-support-ai-widget">';

        // AI Ticket Viewer button
        if ($ticket_data) {
            $ai_viewer_url = admin_url('admin.php?page=fluent-support-ai-ticket-viewer&ticket_id=' . $ticket_data['id']);
            
            $html .= '<a href="' . esc_url($ai_viewer_url) . '" target="_blank" class="button button-primary" style="width: 100%; margin-bottom: 15px; display: block; text-align: center; text-decoration: none;">';
            $html .= '🤖 Open AI Ticket Viewer';
            $html .= '</a>';
        }
        
        // Settings link
        $html .= '<p style="text-align: center; margin-top: 15px;">';
        $html .= '<a href="' . admin_url('admin.php?page=fluent-support-ai-settings') . '" target="_blank" class="button button-link">⚙️ AI Settings</a>';
        $html .= '</p>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Build Customer Tools widget HTML
     */
    private function build_customer_tools_widget($ticket_data = null) {
        $html = '<div id="fluent-support-customer-tools-widget">';

        // Customer Tools
        if ($ticket_data && isset($ticket_data['customer_email']) && !empty($ticket_data['customer_email'])) {
            // Find in Keap link
            $keap_search_url = 'https://app.infusionsoft.com/core/app/searchResults/searchResults?searchTerm=' . urlencode($ticket_data['customer_email']);
            $html .= '<div style="background: #f9f9f9; padding: 8px; margin-bottom: 5px; border-left: 3px solid #0073aa;">';
            $html .= '<a href="' . esc_url($keap_search_url) . '" target="_blank" style="text-decoration: none; color: #0073aa; font-weight: bold; transition: color 0.2s;" onmouseover="this.style.color=\'#0056b3\'" onmouseout="this.style.color=\'#0073aa\'">';
            $html .= '🔍 Find in Keap';
            $html .= '</a>';
            $html .= '</div>';

            // Keap Tags (debug, show up to 5)
            $keap_tags = [];
            $keap_api_key = 'KeapAK-d3a9fe4ce598f45741ff08611a8a3cdfb20c5d9cc1ab824fbe';
            $keap_email = $ticket_data['customer_email'];
            $keap_api_url = 'https://api.infusionsoft.com/crm/rest/v1/contacts?email=' . urlencode($keap_email);
            
            $debug_info = [];
            $debug_info[] = "API URL: " . $keap_api_url;
            $debug_info[] = "Email: " . $keap_email;
            $debug_info[] = "API Key: " . substr($keap_api_key, 0, 10) . "...";

            // Use WordPress HTTP API for a simple GET
            $response = wp_remote_get($keap_api_url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $keap_api_key
                ),
                'timeout' => 10
            ));

            if (is_wp_error($response)) {
                $debug_info[] = "Error: " . $response->get_error_message();
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $debug_info[] = "Response Code: " . $response_code;
                
                $body = wp_remote_retrieve_body($response);
                $debug_info[] = "Response Body Length: " . strlen($body);
                
                if ($response_code == 200) {
                    $data = json_decode($body, true);
                    $debug_info[] = "JSON Decoded: " . (is_array($data) ? "Yes" : "No");
                    
                    if (is_array($data)) {
                        $debug_info[] = "Contacts Count: " . (isset($data['contacts']) ? count($data['contacts']) : "No contacts key");
                        
                        if (!empty($data['contacts'][0]['id'])) {
                            $contact_id = $data['contacts'][0]['id'];
                            $debug_info[] = "Contact ID: " . $contact_id;
                            
                            // Now get tags for this contact
                            $tags_url = 'https://api.infusionsoft.com/crm/rest/v1/contacts/' . intval($contact_id) . '/tags';
                            $debug_info[] = "Tags URL: " . $tags_url;
                            
                            $tags_response = wp_remote_get($tags_url, array(
                                'headers' => array(
                                    'Accept' => 'application/json',
                                    'Authorization' => 'Bearer ' . $keap_api_key
                                ),
                                'timeout' => 10
                            ));
                            
                            if (is_wp_error($tags_response)) {
                                $debug_info[] = "Tags Error: " . $tags_response->get_error_message();
                            } else {
                                $tags_code = wp_remote_retrieve_response_code($tags_response);
                                $debug_info[] = "Tags Response Code: " . $tags_code;
                                
                                $tags_body = wp_remote_retrieve_body($tags_response);
                                $debug_info[] = "Tags Body Length: " . strlen($tags_body);
                                
                                if ($tags_code == 200) {
                                    $tags_data = json_decode($tags_body, true);
                                    $debug_info[] = "Tags JSON Decoded: " . (is_array($tags_data) ? "Yes" : "No");
                                    
                                    if (is_array($tags_data)) {
                                        $debug_info[] = "Tags Data Keys: " . implode(', ', array_keys($tags_data));
                                        $debug_info[] = "Tags Count: " . (isset($tags_data['tags']) ? count($tags_data['tags']) : "No tags key");
                                        
                                        // Debug the actual structure
                                        $debug_info[] = "Raw Tags Data Structure:";
                                        $debug_info[] = print_r($tags_data, true);
                                        
                                        if (!empty($tags_data['tags']) && is_array($tags_data['tags'])) {
                                            $debug_info[] = "First Tag Structure:";
                                            if (isset($tags_data['tags'][0])) {
                                                $debug_info[] = print_r($tags_data['tags'][0], true);
                                            }
                                            
                                            $debug_info[] = "All Tags:";
                                            foreach ($tags_data['tags'] as $index => $tag) {
                                                $debug_info[] = "  Tag $index: " . print_r($tag, true);
                                            }
                                            
                                            // Check for membership tags
                                            $academy_tags = [10142, 10136, 9956, 9954, 9903, 9827, 9821, 9819, 9815, 9813, 9807, 9657];
                                            $homeschool_tags = [7578, 7574];
                                            $jazzpiano_tags = [9403, 9879, 9405];
                                            $pianowithwillie_tags = [7056];
                                            
                                            $has_academy = false;
                                            $has_homeschool = false;
                                            $has_jazzpiano = false;
                                            $has_pianowithwillie = false;
                                            
                                            // Check ALL tags for membership status
                                            foreach ($tags_data['tags'] as $tag) {
                                                $tag_id = '';
                                                $tag_name = '';
                                                
                                                // Handle nested tag structure
                                                if (isset($tag['tag']['id'])) {
                                                    $tag_id = $tag['tag']['id'];
                                                } elseif (isset($tag['id'])) {
                                                    $tag_id = $tag['id'];
                                                }
                                                
                                                if (isset($tag['tag']['name'])) {
                                                    $tag_name = $tag['tag']['name'];
                                                } elseif (isset($tag['name'])) {
                                                    $tag_name = $tag['name'];
                                                }
                                                
                                                // Check membership status
                                                if ($tag_id) {
                                                    if (in_array($tag_id, $academy_tags)) {
                                                        $has_academy = true;
                                                    }
                                                    if (in_array($tag_id, $homeschool_tags)) {
                                                        $has_homeschool = true;
                                                    }
                                                    if (in_array($tag_id, $jazzpiano_tags)) {
                                                        $has_jazzpiano = true;
                                                    }
                                                    if (in_array($tag_id, $pianowithwillie_tags)) {
                                                        $has_pianowithwillie = true;
                                                    }
                                                }
                                            }
                                            
                                            // Display first 5 tags for main display
                                            foreach (array_slice($tags_data['tags'], 0, 5) as $tag) {
                                                $tag_name = '';
                                                
                                                if (isset($tag['tag']['name'])) {
                                                    $tag_name = $tag['tag']['name'];
                                                } elseif (isset($tag['name'])) {
                                                    $tag_name = $tag['name'];
                                                }
                                                
                                                if ($tag_name) {
                                                    $keap_tags[] = esc_html($tag_name);
                                                }
                                            }
                                            
                                            // Add membership status to debug
                                            $debug_info[] = "Membership Status:";
                                            $debug_info[] = "  Academy: " . ($has_academy ? "YES" : "NO");
                                            $debug_info[] = "  HomeSchoolPiano: " . ($has_homeschool ? "YES" : "NO");
                                            $debug_info[] = "  JazzPianoLessons: " . ($has_jazzpiano ? "YES" : "NO");
                                            $debug_info[] = "  PianoWithWillie: " . ($has_pianowithwillie ? "YES" : "NO");
                                        }
                                    }
                                } else {
                                    $debug_info[] = "Tags Response Body: " . substr($tags_body, 0, 200);
                                }
                            }
                        } else {
                            $debug_info[] = "No contact ID found";
                        }
                    }
                } else {
                    $debug_info[] = "Response Body: " . substr($body, 0, 200);
                }
            }

            // Autologin Link button (copy only)
            $contact_id = '';
            $contact_email = $ticket_data['customer_email'];
            
            // Extract contact ID from debug info
            foreach ($debug_info as $info) {
                if (strpos($info, 'Contact ID:') !== false) {
                    $contact_id = trim(str_replace('Contact ID:', '', $info));
                    break;
                }
            }
            
            if ($contact_id && $contact_email) {
                // Dashboard autologin link
                $dashboard_autologin_url = 'https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=' . urlencode($contact_id) . '&Email=' . urlencode($contact_email) . '&redir=/dashboard/';
                $html .= '<div style="background: #f9f9f9; padding: 8px; margin-bottom: 5px; border-left: 3px solid #28a745;">';
                $html .= '<div style="display: flex; align-items: center; gap: 8px;">';
                $html .= '<span style="color: #28a745; font-weight: bold; flex: 1;">🔗 Dashboard Autologin Link</span>';
                $html .= '<button class="copy-autologin-btn" data-url="' . esc_attr($dashboard_autologin_url) . '" style="background: #007cba; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 11px; transition: background 0.2s;" onmouseover="this.style.background=\'#0056b3\'" onmouseout="this.style.background=\'#007cba\'">📋 Copy</button>';
                $html .= '</div>';
                $html .= '<small style="color: #666;">Contact ID: ' . esc_html($contact_id) . ' | Email: ' . esc_html($contact_email) . '</small>';
                $html .= '<div class="copy-success-message" style="display: none; color: #28a745; font-size: 11px; margin-top: 4px;">✅ Copied to clipboard!</div>';
                $html .= '</div>';
                
                // Card Update autologin link
                $card_autologin_url = 'https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id=' . urlencode($contact_id) . '&Email=' . urlencode($contact_email) . '&redir=/card/';
                $html .= '<div style="background: #fff3cd; padding: 8px; margin-bottom: 5px; border-left: 3px solid #ffc107;">';
                $html .= '<div style="display: flex; align-items: center; gap: 8px;">';
                $html .= '<span style="color: #856404; font-weight: bold; flex: 1;">💳 Card Update Autologin Link</span>';
                $html .= '<button class="copy-autologin-btn" data-url="' . esc_attr($card_autologin_url) . '" style="background: #ffc107; color: #212529; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 11px; transition: background 0.2s;" onmouseover="this.style.background=\'#e0a800\'" onmouseout="this.style.background=\'#ffc107\'">📋 Copy</button>';
                $html .= '</div>';
                $html .= '<small style="color: #856404;">Contact ID: ' . esc_html($contact_id) . ' | Email: ' . esc_html($contact_email) . '</small>';
                $html .= '<div class="copy-success-message" style="display: none; color: #28a745; font-size: 11px; margin-top: 4px;">✅ Copied to clipboard!</div>';
                $html .= '</div>';
            }
            
            // Keap Membership Status
            if (isset($has_academy) || isset($has_homeschool) || isset($has_jazzpiano) || isset($has_pianowithwillie)) {
                $html .= '<div style="background: #f9f9f9; padding: 8px; margin-bottom: 5px; border-left: 3px solid #17a2b8;">';
                $html .= '<strong>🏷️ Keap Membership Status:</strong><br>';
                
                if ($has_academy) {
                    $html .= '<span style="color: #28a745; font-weight: bold;">✅ JazzEdge Academy</span><br>';
                }
                if ($has_homeschool) {
                    $html .= '<span style="color: #28a745; font-weight: bold;">✅ HomeSchoolPiano</span><br>';
                }
                if ($has_jazzpiano) {
                    $html .= '<span style="color: #28a745; font-weight: bold;">✅ JazzPianoLessons</span><br>';
                }
                if ($has_pianowithwillie) {
                    $html .= '<span style="color: #28a745; font-weight: bold;">✅ PianoWithWillie (Legacy)</span><br>';
                }
                
                if (!$has_academy && !$has_homeschool && !$has_jazzpiano && !$has_pianowithwillie) {
                    $html .= '<span style="color: #dc3545;">❌ No Active Memberships</span>';
                }
                
                $html .= '</div>';
            }

        }
        
        // JazzEdge Membership Status (compact)
        if ($ticket_data && !empty($ticket_data['customer_email'])) {
            $membership_data = $this->get_jazzedge_data($ticket_data['customer_email']);
            $html .= '<h4 style="margin:10px 0 5px 0;color:#0073aa;">🎹 JazzEdge Membership:</h4>';

            if (!$membership_data) {
                $html .= '<div style="background:#f9f9f9;padding:8px;margin-bottom:5px;border-left:3px solid #dc3545;"><strong>❌ No Connection</strong><br><small style="color:red;">Unable to fetch membership data</small></div>';
            } else {
                $active = !empty($membership_data['memberships']) ? array_filter($membership_data['memberships'], fn($m) => $m['status'] === 'active') : [];
                if ($active) {
                    $html .= '<div style="background:#f9f9f9;padding:8px;margin-bottom:5px;border-left:3px solid #28a745;"><strong>✅ Active Member</strong><br>';
                    foreach ($active as $membership) {
                        $html .= '<small><strong>' . esc_html($membership['product_name']) . '</strong>';
                        if (!empty($membership['expires_at']) && $membership['expires_at'] !== '0000-00-00 00:00:00') {
                            $html .= $membership['expires_at'] === 'lifetime' ? '<br>Lifetime Access' : '<br>Expires: ' . date('M j, Y', strtotime($membership['expires_at']));
                        }
                        $html .= '</small><br>';
                    }
                    $html .= '</div>';
                } else {
                    $html .= '<div style="background:#f9f9f9;padding:8px;margin-bottom:5px;border-left:3px solid #dc3545;"><strong>❌ No Active Membership</strong><br><small>Customer has no active subscriptions</small></div>';
                }

                // Find user_id
                $customer_email = $ticket_data['customer_email'];
                $user_id = !empty($membership_data['user_id']) && is_numeric($membership_data['user_id']) ? intval($membership_data['user_id']) : null;
                if (!$user_id && !empty($membership_data['memberships'])) {
                    foreach ($membership_data['memberships'] as $m) {
                        if (!empty($m['user_id']) && is_numeric($m['user_id'])) {
                            $user_id = intval($m['user_id']);
                            break;
                        }
                    }
                }
                $search_url = 'https://jazzedge.com/wp-admin/users.php?s=' . urlencode($customer_email);
                if ($user_id) {
                    $edit_url = 'https://jazzedge.com/wp-admin/user-edit.php?user_id=' . $user_id . '&wp_http_referer=%2Fwp-admin%2Fusers.php%3Fs%3D' . rawurlencode($customer_email) . '%26action%3D-1%26new_role%26paged%3D1%26action2%3D-1%26new_role2';
                    $html .= '<div style="background:#f9f9f9;padding:8px;margin-bottom:5px;border-left:3px solid #0073aa;"><a href="' . esc_url($edit_url) . '" target="_blank" style="text-decoration:none;color:#0073aa;font-weight:bold;transition:color 0.2s;" onmouseover="this.style.color=\'#0056b3\'" onmouseout="this.style.color=\'#0073aa\'">🧑‍💼 Open at Jazzedge</a></div>';
                } else {
                    $html .= '<div style="background:#f9f9f9;padding:8px;margin-bottom:5px;border-left:3px solid #0073aa;"><a href="' . esc_url($search_url) . '" target="_blank" style="text-decoration:none;color:#0073aa;font-weight:bold;transition:color 0.2s;" onmouseover="this.style.color=\'#0056b3\'" onmouseout="this.style.color=\'#0073aa\'">🧑‍💼 Search at Jazzedge</a></div>';
                }
            }
        }
        
        // Debug info at bottom
        if (isset($debug_info) && !empty($debug_info)) {
            $html .= '<div style="background: #f9f9f9; padding: 8px; margin-top: 10px; border-left: 3px solid #ff9800;">';
            $html .= '<details>';
            $html .= '<summary style="cursor: pointer; font-size: 12px; color: #666;">Debug Info</summary>';
            $html .= '<div style="background: #f0f0f0; padding: 8px; margin-top: 5px; font-size: 11px; font-family: monospace;">';
            foreach ($debug_info as $info) {
                $html .= esc_html($info) . '<br>';
            }
            $html .= '</div>';
            $html .= '</details>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Check rate limit for AI generation
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        $rate_limit_key = 'fluent_support_ai_rate_limit_' . $user_id;
        $rate_limit_data = get_transient($rate_limit_key);
        
        if ($rate_limit_data === false) {
            return true; // No rate limit data, allow request
        }
        
        $current_time = time();
        $requests = $rate_limit_data['requests'];
        $window_start = $rate_limit_data['window_start'];
        
        // Reset window if it's been more than 1 hour
        if ($current_time - $window_start > 3600) {
            return true;
        }
        
        // Allow up to 10 requests per hour per user
        return $requests < 10;
    }
    
    /**
     * Update rate limit counter
     */
    private function update_rate_limit() {
        $user_id = get_current_user_id();
        $rate_limit_key = 'fluent_support_ai_rate_limit_' . $user_id;
        $rate_limit_data = get_transient($rate_limit_key);
        
        $current_time = time();
        
        if ($rate_limit_data === false) {
            $rate_limit_data = array(
                'requests' => 1,
                'window_start' => $current_time
            );
        } else {
            // Reset window if it's been more than 1 hour
            if ($current_time - $rate_limit_data['window_start'] > 3600) {
                $rate_limit_data = array(
                    'requests' => 1,
                    'window_start' => $current_time
                );
            } else {
                $rate_limit_data['requests']++;
            }
        }
        
        set_transient($rate_limit_key, $rate_limit_data, 3600); // 1 hour
    }
    
    /**
     * Validate ticket access
     */
    private function validate_ticket_access($ticket_id) {
        global $wpdb;
        
        // Check if ticket exists
        $ticket = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
                $ticket_id
            )
        );
        
        if (!$ticket) {
            return false;
        }
        
        // Additional access validation can be added here
        // For now, we rely on the capability check in the main method
        return true;
    }
    
    /**
     * Handle ticket viewer requests
     */
    public function handle_ticket_viewer() {
        if (isset($_GET['page']) && $_GET['page'] === 'fluent-support-ai-ticket-viewer') {
            $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;
            
            if (!$ticket_id) {
                wp_die('Invalid ticket ID provided.');
            }
            
            // Security check
            if (!current_user_can('manage_options') && !current_user_can('fluent_support_manage_tickets')) {
                wp_die('Access denied. You do not have permission to view ticket data.');
            }
            
            $this->display_ticket_viewer($ticket_id);
            exit;
        }
    }
    
    /**
     * Handle AI generator requests
     */
    public function handle_ai_generator() {
        if (isset($_GET['page']) && $_GET['page'] === 'fluent-support-ai-generator') {
            $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;
            $prompt_id = isset($_GET['prompt_id']) ? intval($_GET['prompt_id']) : 0;
            
            if (!$ticket_id || !$prompt_id) {
                wp_die('Invalid ticket ID or prompt ID provided.');
            }
            
            // Security check
            if (!current_user_can('manage_options') && !current_user_can('fluent_support_manage_tickets')) {
                wp_die('Access denied. You do not have permission to view ticket data.');
            }
            
            $this->display_ai_generator($ticket_id, $prompt_id);
            exit;
        }
    }

    /**
     * Display ticket viewer page
     */
    private function display_ticket_viewer($ticket_id) {
        // Get ticket data from database
        global $wpdb;
        $ticket_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
            $ticket_id
        ), ARRAY_A);

        if (!$ticket_data) {
            wp_die('Ticket not found.');
        }

        // Get the most recent non-agent content using UNION approach
        $most_recent_content = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                ticket_id,
                title,
                content,
                created_at,
                customer_id,
                agent_id,
                conversation_id,
                person_id,
                content_type
            FROM (
                -- Original ticket content
                SELECT 
                    t.id as ticket_id,
                    t.title,
                    t.content as content,
                    t.created_at as created_at,
                    t.customer_id,
                    t.agent_id,
                    NULL as conversation_id,
                    NULL as person_id,
                    'original_ticket' as content_type
                FROM {$wpdb->prefix}fs_tickets t
                WHERE t.id = %d
                
                UNION ALL
                
                -- All conversations
                SELECT 
                    t.id as ticket_id,
                    t.title,
                    c.content as content,
                    c.created_at as created_at,
                    t.customer_id,
                    t.agent_id,
                    c.id as conversation_id,
                    c.person_id,
                    'conversation' as content_type
                FROM {$wpdb->prefix}fs_tickets t
                INNER JOIN {$wpdb->prefix}fs_conversations c ON t.id = c.ticket_id
                WHERE t.id = %d
            ) combined
            WHERE 
                -- Include original ticket (person_id is NULL)
                person_id IS NULL 
                OR 
                -- Include conversations that are NOT from agents
                person_id NOT IN (
                    SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'agent'
                )
            ORDER BY created_at DESC
            LIMIT 1",
            $ticket_id,
            $ticket_id
        ), ARRAY_A);
        
        // Get all conversations for display (keeping existing logic)
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, p.person_type 
             FROM {$wpdb->prefix}fs_conversations c 
             LEFT JOIN {$wpdb->prefix}fs_persons p ON c.person_id = p.id 
             WHERE c.ticket_id = %d 
             ORDER BY c.created_at ASC",
            $ticket_id
        ), ARRAY_A);

        $ticket_data['conversations'] = $conversations;

        // Get customer name and email from wp_fs_persons
        $customer_name = 'Customer';
        $customer_email = '';
        if (isset($ticket_data['customer_id'])) {
            $customer = $wpdb->get_row($wpdb->prepare(
                "SELECT first_name, email FROM {$wpdb->prefix}fs_persons WHERE id = %d",
                $ticket_data['customer_id']
            ), ARRAY_A);
            
            if ($customer) {
                if (!empty($customer['first_name'])) {
                    $customer_name = $customer['first_name'];
                }
                if (!empty($customer['email'])) {
                    $customer_email = $customer['email'];
                }
            }
        }
        
        // Add customer email to ticket data
        $ticket_data['customer_email'] = $customer_email;
        
        // Get agent name from wp_fs_persons
        $agent_name = 'Support Agent';
        if (isset($ticket_data['agent_id'])) {
            $agent = $wpdb->get_row($wpdb->prepare(
                "SELECT first_name FROM {$wpdb->prefix}fs_persons WHERE id = %d",
                $ticket_data['agent_id']
            ), ARRAY_A);
            
            if ($agent && !empty($agent['first_name'])) {
                $agent_name = $agent['first_name'];
            }
        }
        
        // Build conversation text - use the most recent non-agent content
        $full_conversation = '';
        
        // Get business context
        $business_name = get_option('fluent_support_ai_business_name', '');
        $business_website = get_option('fluent_support_ai_business_website', '');
        $business_industry = get_option('fluent_support_ai_business_industry', '');
        $business_description = get_option('fluent_support_ai_business_description', '');
        $support_tone = get_option('fluent_support_ai_support_tone', 'professional');
        $support_style = get_option('fluent_support_ai_support_style', '');
        
        // Build business context string
        $business_context = '';
        if ($business_name) {
            $business_context .= "Business: $business_name";
        }
        if ($business_industry) {
            $business_context .= ($business_context ? ', ' : '') . "Industry: $business_industry";
        }
        if ($business_website) {
            $business_context .= ($business_context ? ', ' : '') . "Website: $business_website";
        }
        if ($business_description) {
            $business_context .= ($business_context ? ', ' : '') . "Description: $business_description";
        }
        if ($support_tone) {
            $business_context .= ($business_context ? ', ' : '') . "Tone: $support_tone";
        }
        if ($support_style) {
            $business_context .= ($business_context ? ', ' : '') . "Style: $support_style";
        }
        
        if ($most_recent_content) {
            $content = strip_tags($most_recent_content['content']);
            $full_conversation = "($business_context, Customer first name: $customer_name, Agent first name: $agent_name) $content";
        } else {
            // Fallback to original ticket content if no non-agent content found
            $original_content = strip_tags($ticket_data['content']);
            $full_conversation = "($business_context, Customer first name: $customer_name, Agent first name: $agent_name) $original_content";
        }

        $ticket_data['full_conversation_text'] = $full_conversation;

        // Get AI prompts
        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        $prompts = $prompt_manager->get_prompts();

        // Display the ticket viewer
        $this->render_ticket_viewer($ticket_data, $prompts);
    }

    /**
     * Render ticket viewer HTML - MODERN VERSION
     */
    private function render_ticket_viewer($ticket_data, $prompts) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>AI Ticket Viewer - #<?php echo esc_html($ticket_data['id']); ?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * { box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                    margin: 0; padding: 20px; background: #f8f9fa; 
                    line-height: 1.6; color: #333;
                }
                .container { 
                    max-width: 1000px; margin: 0 auto; background: white; 
                    border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
                    overflow: hidden; 
                }
                .header { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    color: white; padding: 24px; text-align: center; 
                }
                .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
                .content { padding: 24px; }
                .ticket-info { 
                    background: #f8f9fa; padding: 20px; border-radius: 8px; 
                    margin-bottom: 24px; border-left: 4px solid #667eea; 
                }
                .ticket-info h2 { margin: 0 0 8px 0; color: #2c3e50; }
                .ticket-info p { margin: 0; color: #6c757d; font-size: 14px; }
                .ticket-content { 
                    background: #e8f5e8; padding: 20px; border-radius: 8px; 
                    margin-bottom: 24px; border-left: 4px solid #28a745; 
                }
                .ticket-content h3 { margin: 0 0 12px 0; color: #155724; font-size: 16px; }
                .ticket-content p { margin: 0; color: #155724; }
                .ai-section { 
                    background: #fff3cd; padding: 24px; border-radius: 8px; 
                    border: 1px solid #ffc107; margin: 24px 0; 
                }
                .ai-section h3 { margin: 0 0 16px 0; color: #856404; }
                .prompt-selector { 
                    display: flex; gap: 12px; align-items: center; margin-bottom: 16px; 
                }
                .prompt-selector select { 
                    flex: 1; padding: 12px; border: 1px solid #ddd; 
                    border-radius: 6px; font-size: 14px; background: white; 
                }
                .generate-btn { 
                    background: #28a745; color: white; padding: 12px 24px; 
                    border: none; border-radius: 6px; cursor: pointer; 
                    font-size: 14px; font-weight: 500; transition: background 0.2s; 
                }
                .generate-btn:hover { background: #218838; }
                .generate-btn:disabled { background: #6c757d; cursor: not-allowed; }
                .ai-response { 
                    background: #d4edda; padding: 20px; border-radius: 8px; 
                    border: 1px solid #28a745; margin: 20px 0; display: none; 
                }
                .ai-response h4 { margin: 0 0 12px 0; color: #155724; }
                .ai-response textarea { 
                    width: 100%; height: 200px; padding: 16px; 
                    border: 1px solid #ddd; border-radius: 6px; 
                    font-size: 14px; line-height: 1.5; font-family: inherit; 
                    resize: vertical; background: white; 
                }
                .copy-btn { 
                    background: #007bff; color: white; padding: 10px 20px; 
                    border: none; border-radius: 6px; cursor: pointer; 
                    font-size: 14px; margin-top: 12px; transition: background 0.2s; 
                }
                .copy-btn:hover { background: #0056b3; }
                .copy-success { 
                    background: #d4edda; color: #155724; padding: 8px 12px; 
                    border-radius: 4px; margin-top: 8px; display: none; 
                    font-size: 14px; 
                }
                .debug-section { 
                    background: #f8f9fa; padding: 16px; border-radius: 6px; 
                    border: 1px solid #dee2e6; margin-top: 16px; 
                }
                .debug-toggle { 
                    background: none; border: none; color: #6c757d; 
                    cursor: pointer; font-size: 14px; text-decoration: underline; 
                    padding: 0; margin-bottom: 8px; 
                }
                .debug-content { display: none; }
                .debug-content.show { display: block; }
                .debug-textarea { 
                    width: 100%; height: 150px; padding: 12px; 
                    border: 1px solid #ddd; border-radius: 4px; 
                    font-size: 12px; font-family: monospace; 
                    background: #f8f9fa; resize: vertical; 
                }
                .loading { 
                    text-align: center; padding: 40px; color: #6c757d; 
                }
                .loading .spinner { 
                    font-size: 24px; margin-bottom: 12px; 
                }
                .error { 
                    background: #f8d7da; color: #721c24; padding: 16px; 
                    border-radius: 6px; border: 1px solid #f5c6cb; 
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🤖 AI Ticket Viewer - #<?php echo esc_html($ticket_data['id']); ?></h1>
                </div>
                
                <div class="content">
                    <!-- Ticket Info -->
                    <div class="ticket-info">
                        <h2><?php echo esc_html($ticket_data['title']); ?></h2>
                        <p><strong>Status:</strong> <?php echo esc_html($ticket_data['status']); ?> | 
                           <strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($ticket_data['created_at'])); ?></p>
                    </div>
                    
                    <!-- Ticket Content -->
                    <div class="ticket-content">
                        <h3>📝 Ticket Content</h3>
                        <p><?php echo esc_html($ticket_data['full_conversation_text']); ?></p>
                    </div>
                    
                    <!-- AI Section -->
                    <div class="ai-section">
                        <h3>🚀 Generate AI Reply</h3>
                        <?php if (!empty($prompts)): ?>
                        <div class="prompt-selector">
                            <select id="prompt-select">
                                <option value="">Select a prompt...</option>
                                <?php foreach ($prompts as $prompt): ?>
                                    <option value="<?php echo esc_attr($prompt['id']); ?>" data-name="<?php echo esc_attr($prompt['name']); ?>">
                                        <?php echo esc_html($prompt['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="generate-btn" onclick="generateResponse()" disabled>Generate</button>
                        </div>
                        <?php else: ?>
                        <p><em>No AI prompts found.</em></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <script>
                // Enable/disable generate button based on prompt selection
                document.getElementById('prompt-select').addEventListener('change', function() {
                    var generateBtn = document.querySelector('.generate-btn');
                    generateBtn.disabled = !this.value;
                });
                
                function generateResponse() {
                    var select = document.getElementById('prompt-select');
                    var promptId = select.value;
                    var promptName = select.options[select.selectedIndex].getAttribute('data-name');
                    
                    if (!promptId) {
                        alert('Please select a prompt first.');
                        return;
                    }
                    
                    // Get the prompt content from the page data
                    var promptContent = '';
                    <?php foreach ($prompts as $prompt): ?>
                        if (promptId == '<?php echo $prompt['id']; ?>') {
                            promptContent = <?php echo json_encode($prompt['prompt']); ?>;
                        }
                    <?php endforeach; ?>
                    
                    if (!promptContent) {
                        alert('Prompt content not found!');
                        return;
                    }
                    
                    // Get conversation text
                    var conversationText = '<?php echo esc_js($ticket_data['full_conversation_text']); ?>';
                    
                    // Generate full prompt
                    var fullPrompt = promptContent.replace('{ticket_content}', conversationText);
                    
                    // Show loading state
                    showAILoading(promptName);
                    
                    // Call AI API
                    callOpenAI(fullPrompt, promptName);
                }
                
                function callOpenAI(prompt, promptName) {
                    // Create form data
                    var formData = new FormData();
                    formData.append('action', 'fluent_support_ai_generate_response');
                    formData.append('prompt', prompt);
                    formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                    
                    // Make AJAX call
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(text => {
                        try {
                            var data = JSON.parse(text);
                            if (data.success) {
                                showAIResponse(promptName, data.data, prompt);
                            } else {
                                showAIError(promptName, data.data || 'AI generation failed');
                            }
                        } catch (e) {
                            showAIError(promptName, 'Invalid response format: ' + text.substring(0, 200));
                        }
                    })
                    .catch(error => {
                        showAIError(promptName, 'Network error: ' + error.message);
                    });
                }
                
                function showAILoading(promptName) {
                    var aiSection = document.getElementById('ai-response-section');
                    if (!aiSection) {
                        aiSection = document.createElement('div');
                        aiSection.id = 'ai-response-section';
                        aiSection.className = 'ai-response';
                        document.querySelector('.content').appendChild(aiSection);
                    }
                    
                    aiSection.innerHTML = 
                        '<h4>🤖 AI Response: ' + promptName + '</h4>' +
                        '<div class="loading">' +
                        '<div class="spinner">⏳</div>' +
                        '<p>Generating AI response...</p>' +
                        '</div>';
                    aiSection.style.display = 'block';
                }
                
                function showAIResponse(promptName, aiResponse, fullPrompt) {
                    var aiSection = document.getElementById('ai-response-section');
                    if (!aiSection) {
                        aiSection = document.createElement('div');
                        aiSection.id = 'ai-response-section';
                        aiSection.className = 'ai-response';
                        document.querySelector('.content').appendChild(aiSection);
                    }
                    
                    aiSection.innerHTML = 
                        '<h4>🤖 AI Response: ' + promptName + '</h4>' +
                        '<textarea readonly id="ai-response-text">' + aiResponse + '</textarea>' +
                        '<button class="copy-btn" onclick="copyAIResponse()">📋 Copy AI Response</button>' +
                        '<div class="copy-success" id="copy-success">✅ Copied to clipboard!</div>' +
                        '<div class="debug-section">' +
                        '<button class="debug-toggle" onclick="toggleDebug()">🔍 Show Debug Info</button>' +
                        '<div class="debug-content" id="debug-content">' +
                        '<textarea readonly class="debug-textarea" id="debug-prompt">' + fullPrompt + '</textarea>' +
                        '</div>' +
                        '</div>';
                    aiSection.style.display = 'block';
                }
                
                function showAIError(promptName, errorMessage) {
                    var aiSection = document.getElementById('ai-response-section');
                    if (!aiSection) {
                        aiSection = document.createElement('div');
                        aiSection.id = 'ai-response-section';
                        aiSection.className = 'ai-response';
                        document.querySelector('.content').appendChild(aiSection);
                    }
                    
                    aiSection.innerHTML = 
                        '<h4>❌ AI Error: ' + promptName + '</h4>' +
                        '<div class="error">' +
                        '<p><strong>Error:</strong> ' + errorMessage + '</p>' +
                        '<p><small>Please check your API key and try again.</small></p>' +
                        '</div>';
                    aiSection.style.display = 'block';
                }
                
                function copyAIResponse() {
                    var textarea = document.getElementById('ai-response-text');
                    textarea.select();
                    document.execCommand('copy');
                    document.getElementById('copy-success').style.display = 'block';
                    setTimeout(function() {
                        document.getElementById('copy-success').style.display = 'none';
                    }, 2000);
                }
                
                function toggleDebug() {
                    var debugContent = document.getElementById('debug-content');
                    var toggleBtn = document.querySelector('.debug-toggle');
                    
                    if (debugContent.classList.contains('show')) {
                        debugContent.classList.remove('show');
                        toggleBtn.textContent = '🔍 Show Debug Info';
                    } else {
                        debugContent.classList.add('show');
                        toggleBtn.textContent = '🔍 Hide Debug Info';
                    }
                }
            </script>
        </body>
        </html>
        <?php
    }

    /**
     * Ticket viewer page callback
     */
    public function ticket_viewer_page() {
        $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;
        
        if (!$ticket_id) {
            wp_die('Invalid ticket ID provided.');
        }
        
        $this->display_ticket_viewer($ticket_id);
    }
    
    /**
     * AI generator page callback
     */
    public function ai_generator_page() {
        $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;
        $prompt_id = isset($_GET['prompt_id']) ? intval($_GET['prompt_id']) : 0;
        
        if (!$ticket_id || !$prompt_id) {
            wp_die('Invalid ticket ID or prompt ID provided.');
        }
        
        $this->display_ai_generator($ticket_id, $prompt_id);
    }

    /**
     * Handle traditional form submission
     */
    public function handle_form_submission() {
        // Handle API key save
        if (isset($_POST['save_openai_key']) && check_admin_referer('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce')) {
            $api_key = sanitize_text_field($_POST['openai_api_key']);
            update_option('fluent_support_ai_openai_key', $api_key);
            echo '<div class="notice notice-success"><p>OpenAI API key saved successfully!</p></div>';
        }
        
        // Handle API key test
        if (isset($_POST['test_openai_key']) && check_admin_referer('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce')) {
            $api_key = get_option('fluent_support_ai_openai_key', '');
            if ($this->test_openai_connection($api_key)) {
                echo '<div class="notice notice-success"><p>OpenAI API key is valid and working!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>OpenAI API key test failed. Please check your key and try again.</p></div>';
            }
        }
        
        // Handle business settings save
        if (isset($_POST['action']) && $_POST['action'] === 'save_business_settings' && check_admin_referer('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce')) {
            $business_name = sanitize_text_field($_POST['business_name']);
            $business_website = esc_url_raw($_POST['business_website']);
            $business_industry = sanitize_text_field($_POST['business_industry']);
            $business_description = sanitize_textarea_field($_POST['business_description']);
            
            update_option('fluent_support_ai_business_name', $business_name);
            update_option('fluent_support_ai_business_website', $business_website);
            update_option('fluent_support_ai_business_industry', $business_industry);
            update_option('fluent_support_ai_business_description', $business_description);
            
            echo '<div class="notice notice-success"><p>Business settings saved successfully!</p></div>';
        }
        
        // Handle style settings save
        if (isset($_POST['action']) && $_POST['action'] === 'save_style_settings' && check_admin_referer('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce')) {
            $support_tone = sanitize_text_field($_POST['support_tone']);
            $support_style = sanitize_textarea_field($_POST['support_style']);
            
            update_option('fluent_support_ai_support_tone', $support_tone);
            update_option('fluent_support_ai_support_style', $support_style);
            
            echo '<div class="notice notice-success"><p>Style settings saved successfully!</p></div>';
        }
        
        // Handle AI reply generation from widget
        if (isset($_POST['action']) && $_POST['action'] === 'generate_ai_reply') {
            if (wp_verify_nonce($_POST['ai_nonce'], 'fluent_support_ai_generate')) {
                $prompt_id = intval($_POST['prompt_id']);
                $ticket_id = intval($_POST['ticket_id']);
                
                if ($prompt_id && $ticket_id) {
                    // Generate AI reply
                    $prompt_manager = new FluentSupportAI_Prompt_Manager();
                    $prompt = $prompt_manager->get_prompt($prompt_id);
                    
                    if ($prompt) {
                        global $wpdb;
                        $ticket_data = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
                            $ticket_id
                        ), ARRAY_A);
                        
                        if ($ticket_data) {
                            $openai_client = new FluentSupportAI_OpenAI_Client();
                            $ticket_content = $ticket_data['content'] . ' ' . $ticket_data['secret_content'];
                            $ai_response = $openai_client->generate_response($prompt['content'], $ticket_content);
                            
                            if ($ai_response && $ai_response['success']) {
                                echo '<div class="notice notice-success"><p><strong>AI Reply Generated:</strong><br><textarea style="width: 100%; height: 200px; margin-top: 10px;">' . esc_textarea($ai_response['content']) . '</textarea></p></div>';
                            } else {
                                $error_message = $ai_response ? $ai_response['message'] : 'Unknown error';
                                echo '<div class="notice notice-error"><p>Failed to generate AI reply: ' . esc_html($error_message) . '</p></div>';
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Test OpenAI connection (like MemberPress plugin)
     */
    private function test_openai_connection($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array('role' => 'user', 'content' => 'Test connection')
                ),
                'max_tokens' => 10
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return isset($data['choices']) && !empty($data['choices']);
    }
    
    /**
     * Debug admin notice (only shown in debug mode)
     */
    public function debug_admin_notice() {
        if (current_user_can('manage_options')) {
            $menu_url = admin_url('admin.php?page=fluent-support-ai-settings');
            echo '<div class="notice notice-info"><p>';
            echo '<strong>Fluent Support AI Debug:</strong> ';
            echo 'Settings page should be available at: <a href="' . esc_url($menu_url) . '">' . esc_url($menu_url) . '</a>';
            echo '</p></div>';
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Include required files first
        $this->include_files();
        
        // Create database tables if needed
        $this->create_tables();
        
        // Set default options
        add_option('fluent_support_ai_version', FLUENT_SUPPORT_AI_VERSION);
        
        // Create default prompts
        $this->create_default_prompts();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fluent_support_ai_prompts';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            prompt text NOT NULL,
            description text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create default prompts
     */
    private function create_default_prompts() {
        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        
        $default_prompts = array(
            array(
                'name' => 'Professional Response',
                'description' => 'Generate a professional and helpful response',
                'prompt' => 'You are a professional customer support agent. Analyze the customer ticket and generate a helpful, professional response that addresses their concerns. Be empathetic, clear, and solution-oriented.'
            ),
            array(
                'name' => 'Technical Support',
                'description' => 'Generate a technical support response',
                'prompt' => 'You are a technical support specialist. Analyze the customer ticket and provide a detailed technical response with step-by-step instructions if needed. Include troubleshooting steps and potential solutions.'
            ),
            array(
                'name' => 'Escalation Response',
                'description' => 'Generate a response for escalated issues',
                'prompt' => 'You are handling an escalated customer support ticket. Generate a response that acknowledges the customer\'s frustration, apologizes for any inconvenience, and provides a clear path forward with next steps.'
            )
        );
        
        foreach ($default_prompts as $prompt_data) {
            $prompt_manager->save_prompt($prompt_data);
        }
    }
    
    /**
     * Display AI generator page
     */
    private function display_ai_generator($ticket_id, $prompt_id) {
        // Get ticket data
        global $wpdb;
        $ticket_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
            $ticket_id
        ), ARRAY_A);

        if (!$ticket_data) {
            wp_die('Ticket not found.');
        }

        // Get conversations
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fs_conversations WHERE ticket_id = %d ORDER BY created_at ASC",
            $ticket_id
        ), ARRAY_A);

        $ticket_data['conversations'] = $conversations;

        // Build conversation text - use the most recent non-agent content
        $full_conversation = '';
        
        if ($most_recent_content) {
            $timestamp = date('M j, Y g:i A', strtotime($most_recent_content['created_at']));
            $content = strip_tags($most_recent_content['content']);
            $full_conversation = "[$timestamp] Customer: $content";
        } else {
            // Fallback to original ticket content if no non-agent content found
            $original_timestamp = date('M j, Y g:i A', strtotime($ticket_data['created_at']));
            $original_content = strip_tags($ticket_data['content']);
            $full_conversation = "[$original_timestamp] Customer: $original_content";
        }

        // Get prompt
        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        $prompt = $prompt_manager->get_prompt($prompt_id);

        if (!$prompt) {
            wp_die('Prompt not found.');
        }

        // Generate full prompt
        $full_prompt = str_replace('{ticket_content}', $full_conversation, $prompt['prompt']);

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>AI Generator - <?php echo esc_html($prompt['name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                textarea { width: 100%; height: 400px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; line-height: 1.4; font-family: monospace; }
                .copy-btn { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; font-size: 16px; }
                .copy-btn:hover { background: #005a87; }
                .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-top: 10px; display: none; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🤖 AI Generator</h1>
                </div>
                <div class="content">
                    <h2>Prompt: <?php echo esc_html($prompt['name']); ?></h2>
                    <h3>Complete Prompt (ready to copy):</h3>
                    <textarea readonly><?php echo esc_textarea($full_prompt); ?></textarea>
                    <br>
                    <button class="copy-btn" onclick="copyToClipboard()">📋 Copy Complete Prompt</button>
                    <div class="success" id="success">✅ Copied to clipboard!</div>
                </div>
            </div>

            <script>
                function copyToClipboard() {
                    var textarea = document.querySelector('textarea');
                    textarea.select();
                    document.execCommand('copy');
                    document.getElementById('success').style.display = 'block';
                    setTimeout(function() {
                        document.getElementById('success').style.display = 'none';
                    }, 2000);
                }
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Generate AI response via AJAX
     */
    public function generate_ai_response() {
        // Debug: Log that this method was called
        error_log('generate_ai_response method called');
        error_log('POST data: ' . print_r($_POST, true));
        
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options') && !current_user_can('fluent_support_manage_tickets')) {
            error_log('Access denied - insufficient permissions');
            wp_send_json_error('Access denied');
        }
        
        $prompt = sanitize_textarea_field($_POST['prompt']);
        error_log('Prompt received: ' . substr($prompt, 0, 100) . '...');
        
        if (empty($prompt)) {
            wp_send_json_error('No prompt provided');
        }
        
        // Get API key
        $api_key = get_option('fluent_support_ai_openai_key');
        
        if (empty($api_key)) {
            wp_send_json_error('OpenAI API key not configured');
        }
        
        // Call OpenAI API
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 1000,
                'temperature' => 0.7
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('API request failed: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            wp_send_json_error('OpenAI API error: ' . $data['error']['message']);
        }
        
        if (!isset($data['choices'][0]['message']['content'])) {
            wp_send_json_error('Invalid API response format');
        }
        
        $ai_response = trim($data['choices'][0]['message']['content']);
        
        wp_send_json_success($ai_response);
    }
    
    /**
     * Save prompt via AJAX
     */
    public function save_prompt_ajax() {
        // Debug: Log what we're receiving
        error_log('save_prompt_ajax called');
        error_log('POST data: ' . print_r($_POST, true));
        
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            error_log('Nonce verification failed in save_prompt_ajax');
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options') && !current_user_can('fluent_support_manage_tickets')) {
            error_log('Access denied in save_prompt_ajax');
            wp_send_json_error('Access denied');
        }

        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        
        // Debug: Check what fields we're getting
        $name = isset($_POST['prompt_name']) ? sanitize_text_field($_POST['prompt_name']) : '';
        $description = isset($_POST['prompt_description']) ? sanitize_textarea_field($_POST['prompt_description']) : '';
        $content = isset($_POST['prompt_content']) ? sanitize_textarea_field($_POST['prompt_content']) : '';
        
        error_log('Name: "' . $name . '"');
        error_log('Description: "' . $description . '"');
        error_log('Content: "' . substr($content, 0, 100) . '..."');
        
        // Prepare data for save_prompt method
        $data = array(
            'prompt_id' => isset($_POST['prompt_id']) ? intval($_POST['prompt_id']) : '',
            'prompt_name' => $name,
            'prompt_description' => $description,
            'prompt_content' => $content
        );

        $result = $prompt_manager->save_prompt($data);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler for deleting prompts
     */
    public function delete_prompt_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options') && !current_user_can('fluent_support_manage_tickets')) {
            wp_send_json_error('Access denied');
        }

        $prompt_id = isset($_POST['prompt_id']) ? intval($_POST['prompt_id']) : 0;
        
        if (!$prompt_id) {
            wp_send_json_error('Invalid prompt ID');
        }

        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        $result = $prompt_manager->delete_prompt($prompt_id);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Get JazzEdge membership data for a customer
     * 
     * @param string $email Customer email address
     * @return array|false Membership data or false on error
     */
    private function get_jazzedge_data($email) {
        if (!is_email($email)) {
            return false;
        }
        
        $cache_key = 'jazzedge_membership_' . md5($email);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        
        $api_url = JAZZEDGE_MAIN_SITE_URL . '/wp-json/jazzedge/v1/membership';
        $api_url = add_query_arg(array(
            'email' => $email,
            'api_key' => JAZZEDGE_API_KEY
        ), $api_url);
        
        $response = wp_remote_get($api_url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$data || !$data['success']) {
            return false;
        }
        
        set_transient($cache_key, $data['data'], 5 * MINUTE_IN_SECONDS);
        return $data['data'];
    }
    
    /**
     * Handle quick links requests
     */
    public function handle_quick_links() {
        if (isset($_GET['page']) && $_GET['page'] === 'fluent-support-ai-quick-links') {
            // Security check
            if (!current_user_can('manage_options')) {
                wp_die('Access denied. You do not have permission to manage quick links.');
            }
        }
    }
    
    /**
     * Quick Links page callback
     */
    public function quick_links_page() {
        $quick_links = get_option('fluent_support_ai_quick_links', array());
        
        ?>
        <div class="wrap">
            <h1>🔗 Quick Links</h1>
            <p>Manage quick links that appear in the admin bar for easy access.</p>
            
            <!-- Add New Quick Link Form -->
            <div class="card" style="max-width: 600px; margin: 20px 0;">
                <h2>Add New Quick Link</h2>
                <form id="add-quick-link-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="link_title">Title</label>
                            </th>
                            <td>
                                <input type="text" id="link_title" name="link_title" class="regular-text" required>
                                <p class="description">The display name for this link</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="link_url">URL</label>
                            </th>
                            <td>
                                <input type="url" id="link_url" name="link_url" class="regular-text" required>
                                <p class="description">The URL this link should point to</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary">Add Quick Link</button>
                    </p>
                </form>
            </div>
            
            <!-- Existing Quick Links -->
            <div class="card" style="max-width: 800px;">
                <h2>Existing Quick Links</h2>
                <?php if (empty($quick_links)): ?>
                    <p>No quick links found. Add your first quick link above.</p>
                <?php else: ?>
                    <div id="quick-links-list" class="sortable-links">
                        <?php foreach ($quick_links as $index => $link): ?>
                            <div class="quick-link-item" data-index="<?php echo esc_attr($index); ?>">
                                <div class="quick-link-handle" title="Drag to reorder">⋮⋮</div>
                                <div class="quick-link-content">
                                    <div class="quick-link-display">
                                        <strong><?php echo esc_html($link['title']); ?></strong>
                                        <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="quick-link-url">
                                            <?php echo esc_html($link['url']); ?>
                                        </a>
                                    </div>
                                    <div class="quick-link-edit" style="display: none;">
                                        <input type="text" class="edit-title" value="<?php echo esc_attr($link['title']); ?>" placeholder="Title">
                                        <input type="url" class="edit-url" value="<?php echo esc_attr($link['url']); ?>" placeholder="URL">
                                    </div>
                                </div>
                                <div class="quick-link-actions">
                                    <button type="button" class="button button-small edit-quick-link" data-index="<?php echo esc_attr($index); ?>">
                                        Edit
                                    </button>
                                    <button type="button" class="button button-small save-quick-link" data-index="<?php echo esc_attr($index); ?>" style="display: none;">
                                        Save
                                    </button>
                                    <button type="button" class="button button-small cancel-edit" data-index="<?php echo esc_attr($index); ?>" style="display: none;">
                                        Cancel
                                    </button>
                                    <button type="button" class="button button-link-delete delete-quick-link" 
                                            data-index="<?php echo esc_attr($index); ?>">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .sortable-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .quick-link-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            transition: all 0.2s ease;
        }
        
        .quick-link-item:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .quick-link-item.ui-sortable-helper {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: rotate(2deg);
        }
        
        .quick-link-handle {
            cursor: move;
            color: #999;
            font-size: 16px;
            line-height: 1;
            padding: 5px;
            user-select: none;
        }
        
        .quick-link-handle:hover {
            color: #666;
        }
        
        .quick-link-content {
            flex: 1;
        }
        
        .quick-link-display {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .quick-link-url {
            color: #0073aa;
            text-decoration: none;
            font-size: 13px;
        }
        
        .quick-link-url:hover {
            text-decoration: underline;
        }
        
        .quick-link-edit {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .quick-link-edit input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        
        .quick-link-actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 10px 0;
            display: none;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 10px 0;
            display: none;
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Quick Link Form
            document.getElementById('add-quick-link-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'fluent_support_ai_save_quick_link');
                formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showMessage('Error adding quick link: ' + (data.data || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    showMessage('Network error. Please try again.', 'error');
                });
            });
            
            // Edit Quick Link
            document.querySelectorAll('.edit-quick-link').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const item = document.querySelector(`[data-index="${index}"]`);
                    const display = item.querySelector('.quick-link-display');
                    const edit = item.querySelector('.quick-link-edit');
                    const editBtn = item.querySelector('.edit-quick-link');
                    const saveBtn = item.querySelector('.save-quick-link');
                    const cancelBtn = item.querySelector('.cancel-edit');
                    
                    display.style.display = 'none';
                    edit.style.display = 'flex';
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'inline-block';
                    cancelBtn.style.display = 'inline-block';
                });
            });
            
            // Save Quick Link
            document.querySelectorAll('.save-quick-link').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const item = document.querySelector(`[data-index="${index}"]`);
                    const title = item.querySelector('.edit-title').value;
                    const url = item.querySelector('.edit-url').value;
                    
                    if (!title || !url) {
                        showMessage('Title and URL are required.', 'error');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('action', 'fluent_support_ai_update_quick_link');
                    formData.append('index', index);
                    formData.append('title', title);
                    formData.append('url', url);
                    formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            showMessage('Error updating quick link: ' + (data.data || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        showMessage('Network error. Please try again.', 'error');
                    });
                });
            });
            
            // Cancel Edit
            document.querySelectorAll('.cancel-edit').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const item = document.querySelector(`[data-index="${index}"]`);
                    const display = item.querySelector('.quick-link-display');
                    const edit = item.querySelector('.quick-link-edit');
                    const editBtn = item.querySelector('.edit-quick-link');
                    const saveBtn = item.querySelector('.save-quick-link');
                    const cancelBtn = item.querySelector('.cancel-edit');
                    
                    display.style.display = 'flex';
                    edit.style.display = 'none';
                    editBtn.style.display = 'inline-block';
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';
                });
            });
            
            // Delete Quick Link
            document.querySelectorAll('.delete-quick-link').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this quick link?')) {
                        const index = this.getAttribute('data-index');
                        const formData = new FormData();
                        formData.append('action', 'fluent_support_ai_delete_quick_link');
                        formData.append('index', index);
                        formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                showMessage('Error deleting quick link: ' + (data.data || 'Unknown error'), 'error');
                            }
                        })
                        .catch(error => {
                            showMessage('Network error. Please try again.', 'error');
                        });
                    }
                });
            });
            
            // Initialize sortable
            if (typeof jQuery !== 'undefined' && jQuery.ui && jQuery.ui.sortable) {
                jQuery('#quick-links-list').sortable({
                    handle: '.quick-link-handle',
                    placeholder: 'quick-link-placeholder',
                    update: function(event, ui) {
                        const order = [];
                        jQuery('#quick-links-list .quick-link-item').each(function() {
                            order.push(jQuery(this).attr('data-index'));
                        });
                        
                        const formData = new FormData();
                        formData.append('action', 'fluent_support_ai_reorder_quick_links');
                        formData.append('order', JSON.stringify(order));
                        formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage('Order updated successfully!', 'success');
                            } else {
                                showMessage('Error updating order: ' + (data.data || 'Unknown error'), 'error');
                            }
                        })
                        .catch(error => {
                            showMessage('Network error. Please try again.', 'error');
                        });
                    }
                });
            }
            
            function showMessage(message, type) {
                const existing = document.querySelector('.success-message, .error-message');
                if (existing) {
                    existing.remove();
                }
                
                const messageDiv = document.createElement('div');
                messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
                messageDiv.textContent = message;
                messageDiv.style.display = 'block';
                
                document.querySelector('.wrap h1').insertAdjacentElement('afterend', messageDiv);
                
                setTimeout(() => {
                    messageDiv.remove();
                }, 3000);
            }
        });
        </script>
        <?php
    }
    
    /**
     * Add quick links to admin bar
     */
    public function add_quick_links_admin_bar($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $quick_links = get_option('fluent_support_ai_quick_links', array());
        
        if (empty($quick_links)) {
            return;
        }
        
        // Add main menu item to the right side (secondary menu)
        $wp_admin_bar->add_menu(array(
            'id' => 'fs-ai-quick-links',
            'title' => 'Quick Links',
            'href' => '#',
            'parent' => 'top-secondary',
            'meta' => array(
                'class' => 'fs-ai-quick-links-menu',
                'title' => 'Quick Links'
            )
        ));
        
        // Add individual links
        foreach ($quick_links as $index => $link) {
            $wp_admin_bar->add_menu(array(
                'id' => 'fs-ai-quick-link-' . $index,
                'parent' => 'fs-ai-quick-links',
                'title' => $link['title'],
                'href' => $link['url'],
                'meta' => array(
                    'target' => '_blank'
                )
            ));
        }
    }
    
    /**
     * Save quick link via AJAX
     */
    public function save_quick_link_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $title = sanitize_text_field($_POST['link_title']);
        $url = esc_url_raw($_POST['link_url']);
        
        if (empty($title) || empty($url)) {
            wp_send_json_error('Title and URL are required');
        }
        
        $quick_links = get_option('fluent_support_ai_quick_links', array());
        $quick_links[] = array(
            'title' => $title,
            'url' => $url
        );
        
        update_option('fluent_support_ai_quick_links', $quick_links);
        
        wp_send_json_success('Quick link added successfully');
    }
    
    /**
     * Delete quick link via AJAX
     */
    public function delete_quick_link_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $index = intval($_POST['index']);
        $quick_links = get_option('fluent_support_ai_quick_links', array());
        
        if (!isset($quick_links[$index])) {
            wp_send_json_error('Quick link not found');
        }
        
        unset($quick_links[$index]);
        $quick_links = array_values($quick_links); // Reindex array
        
        update_option('fluent_support_ai_quick_links', $quick_links);
        
        wp_send_json_success('Quick link deleted successfully');
    }
    
    /**
     * Update quick link via AJAX
     */
    public function update_quick_link_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $index = intval($_POST['index']);
        $title = sanitize_text_field($_POST['title']);
        $url = esc_url_raw($_POST['url']);
        
        if (empty($title) || empty($url)) {
            wp_send_json_error('Title and URL are required');
        }
        
        $quick_links = get_option('fluent_support_ai_quick_links', array());
        
        if (!isset($quick_links[$index])) {
            wp_send_json_error('Quick link not found');
        }
        
        $quick_links[$index] = array(
            'title' => $title,
            'url' => $url
        );
        
        update_option('fluent_support_ai_quick_links', $quick_links);
        
        wp_send_json_success('Quick link updated successfully');
    }
    
    /**
     * Reorder quick links via AJAX
     */
    public function reorder_quick_links_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'fluent_support_ai_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $order = json_decode(stripslashes($_POST['order']), true);
        
        if (!is_array($order)) {
            wp_send_json_error('Invalid order data');
        }
        
        $quick_links = get_option('fluent_support_ai_quick_links', array());
        $reordered_links = array();
        
        foreach ($order as $index) {
            if (isset($quick_links[$index])) {
                $reordered_links[] = $quick_links[$index];
            }
        }
        
        update_option('fluent_support_ai_quick_links', $reordered_links);
        
        wp_send_json_success('Order updated successfully');
    }
}

// Initialize the plugin
function fluent_support_ai() {
    return FluentSupportAI::instance();
}

// Start the plugin
fluent_support_ai();


