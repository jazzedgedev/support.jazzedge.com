<?php
/**
 * Frontend Class for Academy AI Assistant
 * 
 * Handles frontend shortcodes and chat interface
 * Security: Only shows interface to authorized users
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Frontend {
    
    private $feature_flags;
    
    public function __construct() {
        $this->feature_flags = new AAA_Feature_Flags();
        
        // Register shortcode
        add_shortcode('academy_ai_assistant', array($this, 'render_shortcode'));
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // Check if shortcode is used in post content
        global $post;
        $should_enqueue = false;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'academy_ai_assistant')) {
            $should_enqueue = true;
        }
        
        // Also check widgets and other content areas
        if (!$should_enqueue) {
            // Check if we're on a page that might have the shortcode
            // This is a fallback - assets will also be enqueued in render_shortcode if needed
            $should_enqueue = apply_filters('aaa_should_enqueue_assets', false);
        }
        
        if ($should_enqueue) {
            $this->enqueue_scripts_and_styles();
        }
    }
    
    /**
     * Enqueue scripts and styles
     */
    private function enqueue_scripts_and_styles($location = 'main') {
        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');
        
        // Enqueue CSS
        wp_enqueue_style(
            'aaa-assistant-css',
            AAA_PLUGIN_URL . 'assets/css/assistant.css',
            array(),
            AAA_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'aaa-assistant-js',
            AAA_PLUGIN_URL . 'assets/js/assistant.js',
            array('jquery'),
            AAA_VERSION,
            true
        );
        
        // Get WordPress timezone settings
        $timezone_string = get_option('timezone_string');
        $gmt_offset = get_option('gmt_offset');
        
        // Localize script with REST API data
        // Get user avatar (gravatar)
        $user_id = get_current_user_id();
        $user_avatar = '';
        if ($user_id) {
            $user_avatar = get_avatar_url($user_id, array('size' => 40));
        }
        
        wp_localize_script('aaa-assistant-js', 'aaaAssistant', array(
            'restUrl' => rest_url('academy-ai-assistant/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentUserId' => $user_id,
            'location' => sanitize_text_field($location),
            'timezone' => !empty($timezone_string) ? $timezone_string : null,
            'gmtOffset' => $gmt_offset !== false ? floatval($gmt_offset) : 0,
            'userAvatar' => $user_avatar
        ));
    }
    
    /**
     * Render shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_shortcode($atts = array()) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'location' => 'main',
            'show_close_button' => 'false'
        ), $atts, 'academy_ai_assistant');
        
        $location = sanitize_text_field($atts['location']);
        if (empty($location)) {
            $location = 'main';
        }
        
        $show_close_button = filter_var($atts['show_close_button'], FILTER_VALIDATE_BOOLEAN);
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            // Ensure CSS is loaded even for login screen
            $this->enqueue_scripts_and_styles($location);
            
            $login_url = wp_login_url(get_permalink());
            return '<div class="aaa-login-required">
                <div class="aaa-login-message">
                    <div class="aaa-login-icon">🎹</div>
                    <h3>Welcome to Jazzedge AI</h3>
                    <p>Please log in to access your AI Assistant and get personalized help with your jazz piano journey.</p>
                    <a href="' . esc_url($login_url) . '" class="aaa-button aaa-button-primary">
                        <span>Log In</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>';
        }
        
        // Check feature flags
        if (!$this->feature_flags->user_can_access_ai()) {
            return '<div class="aaa-access-denied">
                <div class="aaa-access-message">
                    <h3>Access Restricted</h3>
                    <p>AI Assistant is currently in test mode and not available for your account.</p>
                </div>
            </div>';
        }
        
        // Always enqueue assets when shortcode is rendered
        // This ensures assets load even if shortcode is added dynamically (e.g., Oxygen builder)
        $this->enqueue_scripts_and_styles($location);
        
        // Render chat interface
        ob_start();
        ?>
        <div class="aaa-chat-wrapper" id="aaa-chat-wrapper">
            <!-- Sidebar for chat history -->
            <div class="aaa-chat-sidebar" id="aaa-chat-sidebar">
                <div class="aaa-sidebar-header">
                    <h3>Chat History</h3>
                    <button type="button" class="aaa-new-chat-btn" id="aaa-new-chat-btn" title="Start New Chat">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                </div>
                
                <!-- Token Usage Display -->
                <div class="aaa-token-usage" id="aaa-token-usage">
                    <div class="aaa-token-usage-loading">Loading usage...</div>
                </div>
                
                <div class="aaa-sessions-list" id="aaa-sessions-list">
                    <div class="aaa-sessions-loading">Loading chats...</div>
                </div>
            </div>
            
            <!-- Main chat container -->
            <div class="aaa-chat-container" id="aaa-chat-container">
            <div class="aaa-chat-header">
                <div class="aaa-chat-title">
                    <h2>Jazzedge AI</h2>
                    <span class="aaa-chat-subtitle">Your personal piano learning companion</span>
                </div>
                <div class="aaa-chat-header-actions">
                    <?php if ($show_close_button): ?>
                    <button type="button" class="aaa-close-modal-btn" id="aaa-close-modal-btn" title="Close Jazzedge AI">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Close Jazzedge AI</span>
                    </button>
                    <?php endif; ?>
                    <button type="button" class="aaa-download-transcript-btn" id="aaa-download-transcript-btn" title="Download Chat Transcript">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Download</span>
                    </button>
                </div>
            </div>
            
            <div class="aaa-chat-messages" id="aaa-chat-messages">
                <div class="aaa-welcome-message">
                    <div class="aaa-avatar">
                        🎹
                    </div>
                    <div class="aaa-message-content">
                        <p>Hi! I'm Jazzedge AI, your knowledgeable and friendly music teacher. I'm here to help you learn jazz piano, music theory, and piano technique.</p>
                        <p>How can I help you today?</p>
                    </div>
                </div>
            </div>
            
            <div class="aaa-question-starters" id="aaa-question-starters">
                <p class="aaa-starters-label">Quick start:</p>
                <div class="aaa-starter-chips">
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="learn_topic"
                            data-template="I want to learn {topic} and I am a {level} level player"
                            data-fields='[{"name":"topic","label":"What do you want to learn?","type":"text","placeholder":"e.g., jazz chords, scales, improvisation"},{"name":"level","label":"Your skill level","type":"select","options":["beginner","intermediate","advanced","pro"]}]'>
                        I want to learn...
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="struggling"
                            data-template="I'm struggling with {topic} and need help"
                            data-fields='[{"name":"topic","label":"What are you struggling with?","type":"text","placeholder":"e.g., chord voicings, rhythm, technique"}]'>
                        I'm struggling with...
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="show_lessons"
                            data-template="Show me lessons about {topic}"
                            data-fields='[{"name":"topic","label":"What topic?","type":"text","placeholder":"e.g., blues, swing, bebop"}]'>
                        Show me lessons about...
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="how_to_play"
                            data-template="How do I play {song}?"
                            data-fields='[{"name":"song","label":"What song or piece?","type":"text","placeholder":"e.g., Autumn Leaves, Blue Moon"}]'>
                        How do I play...?
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="explain"
                            data-template="Explain {concept} to me"
                            data-fields='[{"name":"concept","label":"What concept?","type":"text","placeholder":"e.g., ii-V-I progression, voice leading"}]'>
                        Explain...
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="recommend_lessons"
                            data-template="I'm a {level} player, recommend lessons for {topic}"
                            data-fields='[{"name":"level","label":"Your skill level","type":"select","options":["beginner","intermediate","advanced","pro"]},{"name":"topic","label":"What topic?","type":"text","placeholder":"e.g., jazz standards, technique, theory"}]'>
                        Recommend lessons...
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="practice_next"
                            data-template="What should I practice next as a {level} player?"
                            data-fields='[{"name":"level","label":"Your skill level","type":"select","options":["beginner","intermediate","advanced","pro"]}]'>
                        What should I practice next?
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="improve_skill"
                            data-template="Help me improve my {skill}"
                            data-fields='[{"name":"skill","label":"What skill?","type":"select","options":["arranging jazz standards","blues repertoire","chord voicings","comping","ear training","improvisation","jazz and blues licks","jazz repertoire","rhythm","sight reading","slow blues","step-by-step improvisation","technique"]}]'>
                        Improve my...
                    </button>
                    <button type="button" class="aaa-starter-chip" 
                            data-chip-id="show_style"
                            data-template="Show me {style} {type}"
                            data-fields='[{"name":"style","label":"Musical style","type":"select","options":["Any","Jazz","Cocktail","Blues","Rock","Funk","Latin","Classical","Smooth Jazz","Holiday","Ballad","Pop","New Age","Gospel","New Orleans","Country","Modal","Stride","Organ","Boogie"]},{"name":"type","label":"Lesson type","type":"select","options":["licks","chords","songs","scales","techniques"]}]'>
                        Show me style lessons...
                    </button>
                </div>
            </div>
            
            <!-- Modal for chip form -->
            <div class="aaa-chip-modal" id="aaa-chip-modal" style="display: none;">
                <div class="aaa-chip-modal-overlay"></div>
                <div class="aaa-chip-modal-content">
                    <div class="aaa-chip-modal-header">
                        <h3 id="aaa-chip-modal-title">Complete your question</h3>
                        <button type="button" class="aaa-chip-modal-close" id="aaa-chip-modal-close" aria-label="Close">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="aaa-chip-modal-body">
                        <form id="aaa-chip-form">
                            <div id="aaa-chip-form-fields"></div>
                            <div class="aaa-chip-modal-actions">
                                <button type="button" class="button button-secondary" id="aaa-chip-modal-cancel">Cancel</button>
                                <button type="submit" class="button button-primary" id="aaa-chip-modal-submit">Ask JAI</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Modal for lesson link actions -->
            <div class="aaa-link-modal" id="aaa-link-modal" style="display: none;">
                <div class="aaa-link-modal-overlay"></div>
                <div class="aaa-link-modal-content">
                    <div class="aaa-link-modal-header">
                        <h3 id="aaa-link-modal-title">Lesson Options</h3>
                        <button type="button" class="aaa-link-modal-close" id="aaa-link-modal-close" aria-label="Close">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="aaa-link-modal-body">
                        <p id="aaa-link-modal-description" style="margin: 0 0 20px 0; color: #666;"></p>
                        <div class="aaa-link-modal-actions">
                            <button type="button" class="button button-primary" id="aaa-link-modal-visit">Visit Link</button>
                            <button type="button" class="button button-secondary" id="aaa-link-modal-favorite">★ Add to Favorites</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="aaa-chat-input-container">
                <div class="aaa-chat-input-wrapper">
                    <textarea 
                        id="aaa-message-input" 
                        class="aaa-message-input" 
                        placeholder="Type your message here..."
                        rows="1"
                    ></textarea>
                    <button id="aaa-send-button" class="aaa-send-button" type="button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
                <div class="aaa-chat-footer">
                    <span class="aaa-error-message" id="aaa-error-message" style="display: none;"></span>
                </div>
            </div>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
