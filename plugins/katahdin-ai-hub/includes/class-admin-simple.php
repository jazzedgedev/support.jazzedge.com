<?php
/**
 * Admin Interface for Katahdin AI Hub
 * Provides WordPress admin interface for managing the hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Katahdin_AI_Hub_Admin')) {
class Katahdin_AI_Hub_Admin {
    
    private $initialized = false;
    
    /**
     * Initialize Admin Interface
     */
    public function init() {
        if ($this->initialized) {
            return;
        }
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_katahdin_ai_hub_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_katahdin_ai_hub_clear_logs', array($this, 'ajax_clear_logs'));
        
        $this->initialized = true;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Katahdin AI Hub', 'katahdin-ai-hub'),
            __('Katahdin AI Hub', 'katahdin-ai-hub'),
            'manage_options',
            'katahdin-ai-hub',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18l-9-16-9 16z"/><path d="M8 12l2 2 4-4"/><circle cx="18" cy="6" r="1" fill="currentColor"/><circle cx="20" cy="4" r="0.5" fill="currentColor"/><circle cx="16" cy="8" r="0.5" fill="currentColor"/></svg>'),
            30
        );
        
        add_submenu_page(
            'katahdin-ai-hub',
            __('Debug Center', 'katahdin-ai-hub'),
            __('Debug Center', 'katahdin-ai-hub'),
            'manage_options',
            'katahdin-ai-hub-debug',
            array($this, 'debug_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_openai_key');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_usage_limit');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_rate_limit');
        register_setting('katahdin_ai_hub_settings', 'katahdin_ai_hub_enable_logging', array(
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
    }

    /**
     * Sanitize checkbox: save 1 if checked, 0 if unchecked
     */
    public function sanitize_checkbox($value) {
        return !empty($value) ? 1 : 0;
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Katahdin AI Hub Dashboard', 'katahdin-ai-hub'); ?></h1>
            
            <!-- Settings Form -->
            <div class="katahdin-card">
                <h2><?php _e('API Settings', 'katahdin-ai-hub'); ?></h2>
                <form method="post" action="options.php" id="katahdin-settings-form">
                    <?php
                    settings_fields('katahdin_ai_hub_settings');
                    do_settings_sections('katahdin_ai_hub_settings');
                    ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_openai_key"><?php _e('OpenAI API Key', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="katahdin_ai_hub_openai_key" name="katahdin_ai_hub_openai_key" 
                                       value="<?php echo esc_attr(get_option('katahdin_ai_hub_openai_key')); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Get your API key from', 'katahdin-ai-hub'); ?> 
                                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_usage_limit"><?php _e('Global Usage Limit', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="katahdin_ai_hub_usage_limit" name="katahdin_ai_hub_usage_limit" 
                                       value="<?php echo esc_attr(get_option('katahdin_ai_hub_usage_limit', 10000)); ?>" 
                                       class="small-text" min="0" />
                                <p class="description">
                                    <?php _e('Maximum tokens per month across all plugins', 'katahdin-ai-hub'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_rate_limit"><?php _e('Rate Limit', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="katahdin_ai_hub_rate_limit" name="katahdin_ai_hub_rate_limit" 
                                       value="<?php echo esc_attr(get_option('katahdin_ai_hub_rate_limit', 60)); ?>" 
                                       class="small-text" min="1" />
                                <p class="description">
                                    <?php _e('Maximum requests per minute', 'katahdin-ai-hub'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="katahdin_ai_hub_enable_logging"><?php _e('Log AI usage', 'katahdin-ai-hub'); ?></label>
                            </th>
                            <td>
                                <input type="hidden" name="katahdin_ai_hub_enable_logging" value="0" />
                                <input type="checkbox" id="katahdin_ai_hub_enable_logging" name="katahdin_ai_hub_enable_logging" 
                                       value="1" <?php checked(get_option('katahdin_ai_hub_enable_logging', 1), 1); ?> />
                                <p class="description">
                                    <?php _e('Record when students use JAI, AI Practice Analysis, and other AI features. View recent entries in the AI Logs section below. Uncheck to disable.', 'katahdin-ai-hub'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Save Settings', 'katahdin-ai-hub')); ?>
                </form>
            </div>
            
            <!-- API Status -->
            <div class="katahdin-card">
                <h2><?php _e('API Status', 'katahdin-ai-hub'); ?></h2>
                <div class="api-status">
                    <button id="test-api-connection" class="button button-primary">
                        <?php _e('Test API Connection', 'katahdin-ai-hub'); ?>
                    </button>
                    <div id="api-test-result" class="api-test-result"></div>
                </div>
            </div>

            <!-- Logs Management -->
            <div class="katahdin-card">
                <h2><?php _e('AI Logs', 'katahdin-ai-hub'); ?></h2>
                <p class="description">
                    <?php
                    $logs_count = katahdin_ai_hub()->usage_tracker->get_logs_count();
                    printf(
                        /* translators: %d: number of log entries */
                        _n('Currently %d log entry in wp_katahdin_ai_logs.', 'Currently %d log entries in wp_katahdin_ai_logs.', $logs_count, 'katahdin-ai-hub'),
                        number_format_i18n($logs_count)
                    );
                    ?>
                </p>
                <p>
                    <button type="button" id="clear-ai-logs" class="button button-secondary" <?php echo $logs_count === 0 ? ' disabled' : ''; ?>>
                        <?php _e('Clear All Logs', 'katahdin-ai-hub'); ?>
                    </button>
                    <span id="clear-logs-result" class="clear-logs-result"></span>
                </p>
                <?php
                $recent_logs = katahdin_ai_hub()->usage_tracker->get_recent_logs(50);
                if (!empty($recent_logs)) :
                ?>
                <h3 style="margin-top: 20px;"><?php _e('Last 50 entries', 'katahdin-ai-hub'); ?></h3>
                <div class="katahdin-logs-table-wrap">
                    <table class="widefat striped katahdin-logs-table">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Time', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('User', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Plugin', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Level', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Message', 'katahdin-ai-hub'); ?></th>
                                <th><?php _e('Context', 'katahdin-ai-hub'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log) :
                                $context_obj = !empty($log['context']) ? json_decode($log['context']) : null;
                                $username = ($context_obj && isset($context_obj->user_display)) ? $context_obj->user_display : (($context_obj && isset($context_obj->user_email)) ? $context_obj->user_email : '—');
                            ?>
                            <tr>
                                <td><?php echo esc_html($log['id']); ?></td>
                                <td><?php echo esc_html($log['created_at']); ?></td>
                                <td><?php echo esc_html($username); ?></td>
                                <td><?php echo esc_html($log['plugin_id']); ?></td>
                                <td><?php echo esc_html($log['level']); ?></td>
                                <td><?php echo esc_html($log['message']); ?></td>
                                <td>
                                    <?php
                                    if (!empty($log['context'])) {
                                        $context = $log['context'];
                                        $decoded = $context_obj ?? json_decode($context);
                                        if ($decoded) {
                                            $context = wp_json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                        }
                                        echo '<details><summary>' . esc_html__('View', 'katahdin-ai-hub') . '</summary>';
                                        echo '<pre style="max-width: 300px; max-height: 100px; overflow: auto; font-size: 11px;">' . esc_html($context) . '</pre>';
                                        echo '</details>';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($logs_count === 0) : ?>
                <p><?php _e('No log entries.', 'katahdin-ai-hub'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .katahdin-card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin-bottom: 20px;
        }
        
        .katahdin-card h2 {
            margin-top: 0;
            color: #1d2327;
            border-bottom: 1px solid #f0f0f1;
            padding-bottom: 10px;
        }
        
        .api-test-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
            border: 1px solid;
        }
        
        .api-test-result.success {
            background: #d1e7dd;
            color: #0f5132;
            border-color: #badbcc;
        }
        
        .api-test-result.error {
            background: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
        }

        .clear-logs-result {
            margin-left: 10px;
        }
        .clear-logs-result.success { color: #00a32a; }
        .clear-logs-result.error { color: #d63638; }

        .katahdin-logs-table-wrap {
            overflow-x: auto;
            margin-top: 10px;
        }
        .katahdin-logs-table { margin-top: 0; }
        .katahdin-logs-table td, .katahdin-logs-table th { vertical-align: top; }
        .katahdin-logs-table td pre { margin: 0; white-space: pre-wrap; word-break: break-all; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Test API connection
            $('#test-api-connection').on('click', function() {
                var button = $(this);
                var result = $('#api-test-result');
                
                button.prop('disabled', true).text('Testing...');
                result.hide();
                
                $.post(ajaxurl, {
                    action: 'katahdin_ai_hub_test_api',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_hub_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        result.removeClass('error').addClass('success')
                            .html('<strong>Success:</strong> ' + response.data.message)
                            .show();
                    } else {
                        result.removeClass('success').addClass('error')
                            .html('<strong>Error:</strong> ' + response.data)
                            .show();
                    }
                }).always(function() {
                    button.prop('disabled', false).text('Test API Connection');
                });
            });

            // Clear AI logs
            $('#clear-ai-logs').on('click', function() {
                var button = $(this);
                var resultSpan = $('#clear-logs-result');

                button.prop('disabled', true);
                resultSpan.removeClass('success error').text('<?php echo esc_js(__('Clearing...', 'katahdin-ai-hub')); ?>');

                $.post(ajaxurl, {
                    action: 'katahdin_ai_hub_clear_logs',
                    nonce: '<?php echo wp_create_nonce('katahdin_ai_hub_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        var msg = response.data.deleted_count > 0
                            ? '<?php echo esc_js(__('Cleared %d log entries.', 'katahdin-ai-hub')); ?>'.replace('%d', response.data.deleted_count)
                            : '<?php echo esc_js(__('Logs cleared.', 'katahdin-ai-hub')); ?>';
                        resultSpan.addClass('success').text(msg);
                        button.prop('disabled', true);
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        resultSpan.addClass('error').text(response.data || '<?php echo esc_js(__('Error clearing logs.', 'katahdin-ai-hub')); ?>');
                        button.prop('disabled', false);
                    }
                }).fail(function(xhr, status, err) {
                    resultSpan.addClass('error').text('<?php echo esc_js(__('Error clearing logs.', 'katahdin-ai-hub')); ?>');
                    button.prop('disabled', false);
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX: Test API connection
     */
    public function ajax_test_api() {
        check_ajax_referer('katahdin_ai_hub_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $api_key = get_option('katahdin_ai_hub_openai_key');
        
        if (empty($api_key)) {
            wp_send_json_error('API key not configured');
        }
        
        // Test API connection
        $response = wp_remote_get('https://api.openai.com/v1/models', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if ($response_code === 200) {
            wp_send_json_success(array(
                'message' => 'Connection successful!',
                'models_count' => count($response_data['data'] ?? [])
            ));
        } else {
            wp_send_json_error($response_data['error']['message'] ?? 'Connection failed');
        }
    }

    /**
     * AJAX: Clear all AI logs
     */
    public function ajax_clear_logs() {
        check_ajax_referer('katahdin_ai_hub_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'katahdin-ai-hub'));
        }

        $result = katahdin_ai_hub()->usage_tracker->clear_all_logs();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(__('Failed to clear logs', 'katahdin-ai-hub'));
        }
    }
    
    /**
     * Debug page
     */
    public function debug_page() {
        ?>
        <div class="wrap">
            <h1>🔍 Katahdin AI Hub Debug Center</h1>
            <p>Debug and test AI requests to diagnose issues.</p>
            
            <div class="katahdin-debug-sections">
                <!-- Test AI Request Section -->
                <div class="katahdin-debug-section">
                    <h2>🧪 Test AI Request</h2>
                    <p>Test a direct AI request to see exactly what's sent and received.</p>
                    
                    <div class="debug-controls">
                        <div class="debug-input-group">
                            <label for="debug-system-message">System Message:</label>
                            <textarea id="debug-system-message" rows="3" cols="80" class="large-text">You are a helpful piano practice coach. Format responses as 3 separate paragraphs with blank lines between them. Use plain text only.</textarea>
                        </div>
                        
                        <div class="debug-input-group">
                            <label for="debug-user-message">User Message:</label>
                            <textarea id="debug-user-message" rows="8" cols="80" class="large-text">Format your response as exactly 3 separate paragraphs with blank lines between them.

1. STRENGTHS: What they are doing well and their strengths.

2. IMPROVEMENT AREAS: Trends and areas for improvement.

3. NEXT STEPS: Practical next steps and lesson recommendations.

Practice Sessions: 54 sessions
Total Practice Time: 1835 minutes
Average Session Length: 34 minutes
Average Mood/Sentiment: 3.6/5 (1=frustrating, 5=excellent)
Improvement Rate: 68.5% of sessions showed improvement
Most Frequent Practice Day: Friday
Most Practiced Item: Blues Licks
Current Level: 6
Current Streak: 8 days

When recommending lessons, use these titles naturally: Technique - Jazzedge Practice Curriculum™; Improvisation - The Confident Improviser™; Accompaniment - Piano Accompaniment Essentials™; Jazz Standards - Standards By The Dozen™; Super Easy Jazz Standards - Super Simple Standards™.

FORMAT: Write 3 paragraphs separated by blank lines.</textarea>
                        </div>
                        
                        <div class="debug-input-group">
                            <label for="debug-model">Model:</label>
                            <select id="debug-model">
                                <option value="gpt-4">GPT-4</option>
                                <option value="gpt-4-turbo" selected>GPT-4 Turbo</option>
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                            </select>
                        </div>
                        
                        <div class="debug-input-group">
                            <label for="debug-max-tokens">Max Tokens:</label>
                            <input type="number" id="debug-max-tokens" value="1000" min="50" max="4000">
                        </div>
                        
                        <div class="debug-input-group">
                            <label for="debug-temperature">Temperature:</label>
                            <input type="number" id="debug-temperature" value="0.7" min="0" max="2" step="0.1">
                        </div>
                        
                        <div class="debug-buttons">
                            <button type="button" class="button button-primary" onclick="testAIDebug()">Test AI Request</button>
                        </div>
                    </div>
                    
                    <div id="debug-test-results" class="debug-test-results"></div>
                    
                    <!-- Copy Debug Info Button -->
                    <div id="copy-debug-section" style="margin-top: 15px; display: none;">
                        <button type="button" class="button button-secondary" onclick="copyDebugInfo()" id="copy-debug-btn">
                            📋 Copy Debug Info
                        </button>
                        <span id="copy-status" style="margin-left: 10px; color: #666;"></span>
                    </div>
                </div>
                
                <!-- Recent Requests Log -->
                <div class="katahdin-debug-section">
                    <h2>📋 Recent Requests Log</h2>
                    <p>View recent AI requests and responses for debugging.</p>
                    
                    <div id="recent-requests-log" class="recent-requests-log">
                        <p>No recent requests logged. Make a test request above to see logs.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .katahdin-debug-sections {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .katahdin-debug-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
        }
        
        .katahdin-debug-section h2 {
            margin-top: 0;
            color: #1d2327;
        }
        
        .debug-controls {
            display: grid;
            gap: 15px;
        }
        
        .debug-input-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .debug-input-group label {
            font-weight: 600;
            color: #1d2327;
        }
        
        .debug-input-group input,
        .debug-input-group select,
        .debug-input-group textarea {
            padding: 8px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .debug-buttons {
            margin-top: 10px;
        }
        
        .debug-test-results {
            margin-top: 20px;
            padding: 15px;
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            display: none;
        }
        
        .debug-test-results.show {
            display: block;
        }
        
        .recent-requests-log {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 4px;
        }
        
        .request-log-entry {
            margin-bottom: 15px;
            padding: 10px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
        }
        
        .request-log-entry h4 {
            margin: 0 0 10px 0;
            color: #1d2327;
        }
        
        .request-log-entry pre {
            background: #f6f7f7;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
            margin: 5px 0;
        }
        </style>
        
        <script>
        function testAIDebug() {
            const systemMessage = document.getElementById('debug-system-message').value;
            const userMessage = document.getElementById('debug-user-message').value;
            const model = document.getElementById('debug-model').value;
            const maxTokens = document.getElementById('debug-max-tokens').value;
            const temperature = document.getElementById('debug-temperature').value;
            
            const resultsDiv = document.getElementById('debug-test-results');
            const copySection = document.getElementById('copy-debug-section');
            
            resultsDiv.innerHTML = '<p>Testing AI request...</p>';
            resultsDiv.classList.add('show');
            copySection.style.display = 'none';
            
            const requestData = {
                messages: [
                    { role: 'system', content: systemMessage },
                    { role: 'user', content: userMessage }
                ],
                model: model,
                max_tokens: parseInt(maxTokens),
                temperature: parseFloat(temperature)
            };
            
            fetch('/wp-json/katahdin-ai-hub/v1/debug/chat/completions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                const timestamp = new Date().toLocaleString();
                let html = '<h3>Test Results - ' + timestamp + '</h3>';
                
                if (data.success) {
                    html += '<div style="background: #d1e7dd; padding: 10px; border-radius: 4px; margin: 10px 0;">';
                    html += '<strong>✅ Success!</strong><br>';
                    html += 'Response: ' + data.data.choices[0].message.content;
                    html += '</div>';
                } else {
                    html += '<div style="background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;">';
                    html += '<strong>❌ Error:</strong> ' + (data.message || 'Unknown error');
                    html += '</div>';
                }
                
                html += '<h4>Request Sent:</h4>';
                html += '<pre>' + JSON.stringify(requestData, null, 2) + '</pre>';
                
                html += '<h4>Raw Response:</h4>';
                html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                
                resultsDiv.innerHTML = html;
                copySection.style.display = 'block';
                
                // Log to recent requests
                logRecentRequest(timestamp, requestData, data);
            })
            .catch(error => {
                const timestamp = new Date().toLocaleString();
                let html = '<h3>Test Results - ' + timestamp + '</h3>';
                html += '<div style="background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;">';
                html += '<strong>❌ Network Error:</strong> ' + error.message;
                html += '</div>';
                
                html += '<h4>Request Sent:</h4>';
                html += '<pre>' + JSON.stringify(requestData, null, 2) + '</pre>';
                
                resultsDiv.innerHTML = html;
                copySection.style.display = 'block';
                
                // Log to recent requests
                logRecentRequest(timestamp, requestData, { error: error.message });
            });
        }
        
        function copyDebugInfo() {
            const resultsDiv = document.getElementById('debug-test-results');
            const statusSpan = document.getElementById('copy-status');
            
            // Strip HTML tags and copy text content
            const textContent = resultsDiv.innerText || resultsDiv.textContent;
            
            navigator.clipboard.writeText(textContent).then(() => {
                statusSpan.textContent = 'Copied to clipboard!';
                statusSpan.style.color = '#00a32a';
                
                setTimeout(() => {
                    statusSpan.textContent = '';
                }, 2000);
            }).catch(err => {
                statusSpan.textContent = 'Failed to copy';
                statusSpan.style.color = '#d63638';
                
                setTimeout(() => {
                    statusSpan.textContent = '';
                }, 2000);
            });
        }
        
        function logRecentRequest(timestamp, request, response) {
            const logDiv = document.getElementById('recent-requests-log');
            
            if (logDiv.innerHTML.includes('No recent requests logged')) {
                logDiv.innerHTML = '';
            }
            
            const entry = document.createElement('div');
            entry.className = 'request-log-entry';
            
            let entryHtml = '<h4>Request - ' + timestamp + '</h4>';
            entryHtml += '<strong>Model:</strong> ' + request.model + '<br>';
            entryHtml += '<strong>Max Tokens:</strong> ' + request.max_tokens + '<br>';
            entryHtml += '<strong>Temperature:</strong> ' + request.temperature + '<br>';
            
            if (response.success) {
                entryHtml += '<strong>Status:</strong> <span style="color: #00a32a;">Success</span><br>';
                entryHtml += '<strong>Response:</strong> ' + (response.data.choices[0].message.content.substring(0, 100) + '...') + '<br>';
            } else {
                entryHtml += '<strong>Status:</strong> <span style="color: #d63638;">Error</span><br>';
                entryHtml += '<strong>Error:</strong> ' + (response.message || response.error || 'Unknown error') + '<br>';
            }
            
            entryHtml += '<details><summary>Full Request/Response</summary>';
            entryHtml += '<h5>Request:</h5><pre>' + JSON.stringify(request, null, 2) + '</pre>';
            entryHtml += '<h5>Response:</h5><pre>' + JSON.stringify(response, null, 2) + '</pre>';
            entryHtml += '</details>';
            
            entry.innerHTML = entryHtml;
            logDiv.insertBefore(entry, logDiv.firstChild);
            
            // Keep only last 10 entries
            const entries = logDiv.querySelectorAll('.request-log-entry');
            if (entries.length > 10) {
                entries[entries.length - 1].remove();
            }
        }
        </script>
        <?php
    }
}
}
