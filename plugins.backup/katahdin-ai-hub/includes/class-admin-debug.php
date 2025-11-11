<?php
/**
 * Debug Admin Interface for Katahdin AI Hub
 * Provides debug tools for testing AI requests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Katahdin_AI_Hub_Admin_Debug')) {
class Katahdin_AI_Hub_Admin_Debug {
    
    /**
     * Initialize Debug Admin Interface
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add debug submenu to existing admin menu
     */
    public function add_admin_menu() {
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
            
            fetch('/wp-json/katahdin-ai-hub/v1/chat/completions', {
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
