<?php
/**
 * Admin Interface for Keap To Fluent Tagging
 * 
 * Provides settings page to configure authentication code
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class KTF_Admin {
    
    /**
     * Initialize admin interface
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'Keap To Fluent Tagging',
            'Keap To Fluent Tagging',
            'manage_options',
            'keap-to-fluent-tagging',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('ktf_settings', 'ktf_auth_code', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        
        register_setting('ktf_settings', 'ktf_debug_enabled', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        ));
    }
    
    /**
     * Get debug logs from WordPress debug.log file
     */
    private function get_debug_logs($limit = 200) {
        $debug_log_file = WP_CONTENT_DIR . '/debug.log';
        $logs = array();
        
        if (!file_exists($debug_log_file) || !is_readable($debug_log_file)) {
            return $logs;
        }
        
        // For large files, read from the end
        $file_size = filesize($debug_log_file);
        $handle = fopen($debug_log_file, 'r');
        
        if (!$handle) {
            return $logs;
        }
        
        // If file is small (< 2MB), read it all
        if ($file_size < 2 * 1024 * 1024) {
            $file = file($debug_log_file);
            fclose($handle);
            
            if (!$file) {
                return $logs;
            }
            
            // Reverse to get most recent first
            $file = array_reverse($file);
            
            foreach ($file as $line) {
                if (strpos($line, 'KTF:') !== false) {
                    if (preg_match('/\[([^\]]+)\]\s+KTF:\s*(.+)$/', $line, $matches)) {
                        $logs[] = array(
                            'timestamp' => $matches[1],
                            'message' => trim($matches[2])
                        );
                        
                        if (count($logs) >= $limit) {
                            break;
                        }
                    }
                }
            }
        } else {
            // For large files, read backwards from the end
            $chunk_size = 8192; // 8KB chunks
            $position = max(0, $file_size - ($chunk_size * 10)); // Read last ~80KB
            fseek($handle, $position);
            
            $content = fread($handle, $file_size - $position);
            fclose($handle);
            
            $lines = explode("\n", $content);
            $lines = array_reverse($lines);
            
            foreach ($lines as $line) {
                if (strpos($line, 'KTF:') !== false) {
                    if (preg_match('/\[([^\]]+)\]\s+KTF:\s*(.+)$/', $line, $matches)) {
                        $logs[] = array(
                            'timestamp' => $matches[1],
                            'message' => trim($matches[2])
                        );
                        
                        if (count($logs) >= $limit) {
                            break;
                        }
                    }
                }
            }
        }
        
        // Reverse back to chronological order (oldest first)
        return array_reverse($logs);
    }
    
    /**
     * Clear KTF entries from debug log
     */
    private function clear_debug_logs() {
        $debug_log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log_file) || !is_readable($debug_log_file)) {
            return false;
        }
        
        if (!is_writable($debug_log_file)) {
            return false;
        }
        
        // Read the file
        $file = file($debug_log_file);
        if (!$file) {
            return false;
        }
        
        // Filter out KTF entries
        $filtered_lines = array();
        foreach ($file as $line) {
            // Keep lines that don't contain KTF:
            if (strpos($line, 'KTF:') === false) {
                $filtered_lines[] = $line;
            }
        }
        
        // Write back to file
        $result = file_put_contents($debug_log_file, implode('', $filtered_lines));
        
        return $result !== false;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle clear logs action
        if (isset($_POST['ktf_clear_logs']) && check_admin_referer('ktf_clear_logs')) {
            $cleared = $this->clear_debug_logs();
            if ($cleared) {
                echo '<div class="notice notice-success is-dismissible"><p>Debug logs cleared successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to clear debug logs. Please check file permissions.</p></div>';
            }
        }
        
        // Handle form submission
        if (isset($_POST['ktf_save_settings']) && check_admin_referer('ktf_save_settings')) {
            $auth_code = isset($_POST['ktf_auth_code']) ? sanitize_text_field($_POST['ktf_auth_code']) : '';
            $debug_enabled = isset($_POST['ktf_debug_enabled']) ? 1 : 0;
            
            update_option('ktf_auth_code', $auth_code);
            update_option('ktf_debug_enabled', $debug_enabled);
            
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }
        
        // Get current settings
        $auth_code = get_option('ktf_auth_code', '');
        $debug_enabled = get_option('ktf_debug_enabled', false);
        
        // Get endpoint URL
        $endpoint_url = rest_url('ktf/v1/tag');
        
        ?>
        <div class="wrap">
            <h1>Keap To Fluent Tagging Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('ktf_save_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ktf_auth_code">Authentication Code</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="ktf_auth_code" 
                                   name="ktf_auth_code" 
                                   value="<?php echo esc_attr($auth_code); ?>" 
                                   class="regular-text" 
                                   placeholder="Enter authentication code" />
                            <p class="description">
                                This code will be sent by Keap in the 'code' field to authenticate requests.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ktf_debug_enabled">Enable Debug Logging</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="ktf_debug_enabled" 
                                       name="ktf_debug_enabled" 
                                       value="1" 
                                       <?php checked($debug_enabled, true); ?> />
                                Enable debug logging for troubleshooting
                            </label>
                            <p class="description">
                                When enabled, all requests will be logged to the debug log. Make sure WordPress debug logging is enabled in wp-config.php.
                            </p>
                        </td>
                    </tr>
                </table>
                
                <h2>Endpoint Information</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Endpoint URL</th>
                        <td>
                            <code><?php echo esc_html($endpoint_url); ?></code>
                            <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js($endpoint_url); ?>'); alert('URL copied to clipboard!');">
                                Copy URL
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Method</th>
                        <td><code>POST</code></td>
                    </tr>
                    <tr>
                        <th scope="row">Required Fields</th>
                        <td>
                            <ul>
                                <li><code>email</code> - Contact email address</li>
                                <li><code>tag_id</code> - FluentCRM tag ID (numeric)</li>
                                <li><code>code</code> - Authentication code (must match the code above)</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Optional Fields</th>
                        <td>
                            <ul>
                                <li><code>action</code> - Either "add" or "delete" (defaults to "add" if not provided)</li>
                                <li><code>tag_id2</code> - A second tag ID that will <strong>always be added</strong> regardless of the action field. Useful for removing a membership tag but adding a follow-up tag.</li>
                            </ul>
                            <p><strong>Important:</strong> <code>tag_id2</code> always adds the tag, even if <code>action</code> is "delete". This allows you to remove one tag (tag_id) while simultaneously adding another tag (tag_id2) for follow-up purposes.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Request Format</th>
                        <td>
                            <p><strong>Add Tag (default):</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "email": "user@example.com",
  "tag_id": 123,
  "code": "your-authentication-code",
  "action": "add"
}</pre>
                            <p><strong>Remove Tag:</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "email": "user@example.com",
  "tag_id": 123,
  "code": "your-authentication-code",
  "action": "delete"
}</pre>
                            <p><strong>Remove Tag and Add Follow-up Tag (tag_id2):</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "email": "user@example.com",
  "tag_id": 123,
  "tag_id2": 456,
  "code": "your-authentication-code",
  "action": "delete"
}</pre>
                            <p><em>Note: If "action" is omitted, it defaults to "add". The tag_id2 will always be added regardless of the action value.</em></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Response Format</th>
                        <td>
                            <p><strong>Success (200):</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "success": true,
  "message": "Contact tagged successfully",
  "action": "add",
  "contact_id": 456,
  "email": "user@example.com",
  "tag_id": 123
}</pre>
                            <p><strong>Success with tag_id2 (200):</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "success": true,
  "message": "Contact untagged with tag_id 123. Contact tagged with tag_id2 456.",
  "action": "delete",
  "contact_id": 456,
  "email": "user@example.com",
  "tag_id": 123,
  "tag_id2": 456
}</pre>
                            <p><em>For delete action, message will be "Contact untagged successfully" and action will be "delete". If tag_id2 is provided, it will be included in the response and the message will reflect both operations.</em></p>
                            <p><strong>Error (400/401/500):</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "code": "error_code",
  "message": "Error message",
  "data": {
    "status": 400
  }
}</pre>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Settings', 'primary', 'ktf_save_settings'); ?>
            </form>
            
            <h2>Debug Log Viewer</h2>
            <?php
            $debug_enabled = get_option('ktf_debug_enabled', false);
            $debug_logs = $this->get_debug_logs();
            $log_count = count($debug_logs);
            ?>
            
            <?php
            $wp_debug_log_enabled = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
            ?>
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-bottom: 20px;">
                <strong>Debug Status:</strong> 
                <?php if ($debug_enabled): ?>
                    <span style="color: #28a745;">Enabled</span>
                <?php else: ?>
                    <span style="color: #dc3545;">Disabled</span>
                <?php endif; ?>
                <p style="margin: 5px 0 0 0; font-size: 13px;">
                    <?php if ($debug_enabled): ?>
                        Debug logging is active. All requests will be logged.
                        <?php if (!$wp_debug_log_enabled): ?>
                            <br><strong style="color: #dc3545;">Warning:</strong> WordPress debug logging (WP_DEBUG_LOG) is not enabled. Logs will not be saved.
                        <?php endif; ?>
                    <?php else: ?>
                        Debug logging is disabled. Enable it above to start logging requests.
                    <?php endif; ?>
                </p>
            </div>
            
            <p>
                <strong>Showing last <?php echo $log_count; ?> KTF log entries</strong>
                <a href="<?php echo esc_url(admin_url('options-general.php?page=keap-to-fluent-tagging')); ?>" class="button button-small" style="margin-left: 10px;">Refresh</a>
                <?php if ($log_count > 0): ?>
                    <form method="post" action="" style="display: inline-block; margin-left: 10px;">
                        <?php wp_nonce_field('ktf_clear_logs'); ?>
                        <button type="submit" name="ktf_clear_logs" class="button button-small" onclick="return confirm('Are you sure you want to clear all KTF debug logs? This action cannot be undone.');" style="color: #dc3545;">
                            Clear Logs
                        </button>
                    </form>
                <?php endif; ?>
            </p>
            
            <?php if (empty($debug_logs)): ?>
                <div style="max-height: 600px; overflow-y: auto;">
                    <p>No logs to display.</p>
                </div>
            <?php else: ?>
                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($debug_logs as $log): ?>
                        <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                            <div style="margin-bottom: 5px;">
                                <?php echo esc_html($log['timestamp']); ?>
                            </div>
                            <div style="white-space: pre-wrap; word-wrap: break-word;">
                                <?php echo esc_html($log['message']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <p style="margin-top: 15px;"><strong>Note:</strong> Debug logs include:</p>
            <ul>
                <li>Request method and URI</li>
                <li>All headers</li>
                <li>Raw request body</li>
                <li>JSON params, body params, and query params</li>
                <li>Validation steps</li>
                <li>FluentCRM operations</li>
                <li>Success/error messages</li>
            </ul>
            
            <h2>How It Works</h2>
            <ol>
                <li>Configure an authentication code above and save settings.</li>
                <li>Set up a webhook in Keap that sends POST requests to the endpoint URL above.</li>
                <li>Include the following fields in your Keap webhook payload:
                    <ul>
                        <li><code>email</code> - The contact's email address</li>
                        <li><code>tag_id</code> - The FluentCRM tag ID to apply or remove</li>
                        <li><code>code</code> - The authentication code you configured</li>
                        <li><code>action</code> - Either "add" or "delete" (optional, defaults to "add")</li>
                        <li><code>tag_id2</code> - Optional second tag ID that will <strong>always be added</strong> regardless of the action. Useful for removing a membership tag but adding a follow-up tag.</li>
                    </ul>
                </li>
                <li>If the action is "add" OR if tag_id2 is provided, and the contact doesn't exist in FluentCRM, it will be created automatically.</li>
                <li>The contact will be tagged or untagged based on the action specified for tag_id.</li>
                <li><strong>Important:</strong> If tag_id2 is provided, it will always be added to the contact, even if action is "delete". This allows you to remove one tag while adding another for follow-up purposes (e.g., removing membership tag but adding a "follow-up" tag).</li>
            </ol>
            
            <?php if (empty($debug_logs)): ?>
                <h2>Debug Logging Setup</h2>
                <div class="notice notice-info">
                    <p><strong>No debug logs found.</strong> Make sure:</p>
                    <ol>
                        <li>WordPress debug logging is enabled in <code>wp-config.php</code>:
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; margin-top: 10px;">define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);</pre>
                        </li>
                        <li>At least one request has been made to the endpoint</li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

