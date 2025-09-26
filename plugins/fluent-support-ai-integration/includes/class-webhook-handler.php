<?php
/**
 * Webhook Handler for Fluent Support AI Integration
 * 
 * Handles incoming webhooks to apply tags to FluentCRM contacts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FluentSupportAI_Webhook_Handler {
    
    /**
     * Initialize webhook handler
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_webhook_endpoints'));
        add_action('admin_menu', array($this, 'add_webhook_admin_menu'));
        add_action('admin_init', array($this, 'handle_webhook_settings'));
    }
    
    /**
     * Register REST API endpoints for webhooks
     */
    public function register_webhook_endpoints() {
        // Main webhook endpoint
        register_rest_route('fluent-support-ai/v1', '/webhook/(?P<webhook_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook_request'),
            'permission_callback' => array($this, 'verify_webhook_permission'),
            'args' => array(
                'webhook_id' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return is_string($param) && !empty($param);
                    }
                ),
            ),
        ));
        
        // Test webhook endpoint
        register_rest_route('fluent-support-ai/v1', '/webhook/(?P<webhook_id>[a-zA-Z0-9_-]+)/test', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_webhook'),
            'permission_callback' => array($this, 'verify_webhook_permission'),
        ));
    }
    
    /**
     * Verify webhook permission
     */
    public function verify_webhook_permission($request) {
        $webhook_id = $request->get_param('webhook_id');
        $webhook_config = $this->get_webhook_config($webhook_id);
        
        if (!$webhook_config) {
            return new WP_Error('webhook_not_found', 'Webhook not found', array('status' => 404));
        }
        
        // Check if webhook is active
        if (!$webhook_config['active']) {
            return new WP_Error('webhook_inactive', 'Webhook is inactive', array('status' => 403));
        }
        
        // Verify webhook secret if configured
        if (!empty($webhook_config['secret'])) {
            $provided_secret = $request->get_header('X-Webhook-Secret');
            if ($provided_secret !== $webhook_config['secret']) {
                return new WP_Error('invalid_secret', 'Invalid webhook secret', array('status' => 401));
            }
        }
        
        return true;
    }
    
    /**
     * Handle webhook request
     */
    public function handle_webhook_request($request) {
        $webhook_id = $request->get_param('webhook_id');
        $webhook_config = $this->get_webhook_config($webhook_id);
        
        // Get payload from JSON body first, then fall back to query parameters
        $payload = $request->get_json_params();
        
        // If no JSON payload, try to get data from query parameters
        if (!is_array($payload) || empty($payload)) {
            $query_params = $request->get_query_params();
            if (!empty($query_params)) {
                $payload = $query_params;
            }
        }
        
        // If still no payload, try POST parameters
        if (!is_array($payload) || empty($payload)) {
            $post_params = $request->get_body_params();
            if (!empty($post_params)) {
                $payload = $post_params;
            }
        }
        
        // Get keap_id from query parameters if provided
        $keap_id = $request->get_param('keap_id');
        if ($keap_id) {
            $payload['keap_id'] = $keap_id;
        }
        
        // Validate payload
        if (!is_array($payload) || empty($payload)) {
            $this->log_webhook_request($webhook_id, array(
                'error' => 'No payload data found',
                'json_params' => $request->get_json_params(),
                'query_params' => $request->get_query_params(),
                'body_params' => $request->get_body_params(),
                'method' => $request->get_method()
            ), 'error', array(
                'step' => 'validate_payload'
            ));
            
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No payload data found. Expected JSON body, query parameters, or POST data.'
            ), 400);
        }
        
        // Log webhook request
        $this->log_webhook_request($webhook_id, $payload, 'received', array(
            'webhook_config' => $webhook_config,
            'payload_size' => strlen(json_encode($payload))
        ));
        
        try {
            // Extract contact identifier from payload
            $contact_identifier = $this->extract_contact_identifier($payload, $webhook_config);
            
            if (!$contact_identifier) {
                $payload_keys = is_array($payload) ? array_keys($payload) : array();
                $payload_sample = is_array($payload) ? array_slice($payload, 0, 3, true) : $payload;
                
                $this->log_webhook_request($webhook_id, array(
                    'error' => 'Contact identifier not found in payload',
                    'identifier_field' => $webhook_config['identifier_field'],
                    'payload_keys' => $payload_keys,
                    'payload_sample' => $payload_sample,
                    'payload_type' => gettype($payload)
                ), 'error', array(
                    'step' => 'extract_identifier',
                    'webhook_config' => $webhook_config
                ));
                throw new Exception('Contact identifier not found in payload. Looking for field: ' . $webhook_config['identifier_field']);
            }
            
            // Look up contact in FluentCRM
            $contact = $this->lookup_fluentcrm_contact($contact_identifier);
            
            if (!$contact) {
                // Try to create the contact if not found
                $contact = $this->create_fluentcrm_contact($payload, $contact_identifier);
                
                if (!$contact) {
                    $this->log_webhook_request($webhook_id, array(
                        'error' => 'Contact not found and could not be created in FluentCRM',
                        'contact_identifier' => $contact_identifier,
                        'identifier_type' => is_email($contact_identifier) ? 'email' : (is_numeric($contact_identifier) ? 'id' : 'custom'),
                        'payload' => $payload
                    ), 'error', array(
                        'step' => 'create_contact',
                        'contact_identifier' => $contact_identifier
                    ));
                    throw new Exception('Contact not found and could not be created in FluentCRM: ' . $contact_identifier);
                } else {
                    // Log successful contact creation
                    $this->log_webhook_request($webhook_id, array(
                        'message' => 'Contact created successfully',
                        'contact_id' => $contact->id,
                        'contact_email' => $contact->email,
                        'contact_identifier' => $contact_identifier
                    ), 'success', array(
                        'step' => 'create_contact',
                        'contact_identifier' => $contact_identifier
                    ));
                }
            } else {
                // Update existing contact with custom fields if provided
                $this->update_contact_custom_fields($contact, $payload);
            }
            
            // Apply tag to contact
            $result = $this->apply_tag_to_contact($contact, $webhook_config['tag_id']);
            
            if ($result) {
                $this->log_webhook_request($webhook_id, array(
                    'contact_id' => $contact->id,
                    'contact_email' => $contact->email,
                    'contact_first_name' => $contact->first_name ?? 'N/A',
                    'contact_last_name' => $contact->last_name ?? 'N/A',
                    'tag_id' => $webhook_config['tag_id'],
                    'tag_name' => $webhook_config['tag_name'],
                    'existing_tags' => $contact->tags ?? array()
                ), 'success', array(
                    'step' => 'apply_tag',
                    'contact_id' => $contact->id
                ));
                
                return new WP_REST_Response(array(
                    'success' => true,
                    'message' => 'Tag applied successfully',
                    'contact_id' => $contact->id,
                    'contact_email' => $contact->email,
                    'tag_name' => $webhook_config['tag_name']
                ), 200);
            } else {
                $this->log_webhook_request($webhook_id, array(
                    'error' => 'Failed to apply tag to contact',
                    'contact_id' => $contact->id,
                    'contact_email' => $contact->email,
                    'tag_id' => $webhook_config['tag_id'],
                    'tag_name' => $webhook_config['tag_name']
                ), 'error', array(
                    'step' => 'apply_tag',
                    'contact_id' => $contact->id
                ));
                throw new Exception('Failed to apply tag to contact');
            }
            
        } catch (Exception $e) {
            $this->log_webhook_request($webhook_id, array(
                'error' => $e->getMessage(),
                'payload' => $payload,
                'webhook_config' => $webhook_config
            ), 'error', array(
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
            
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $e->getMessage()
            ), 400);
        }
    }
    
    /**
     * Test webhook endpoint
     */
    public function test_webhook($request) {
        $webhook_id = $request->get_param('webhook_id');
        $webhook_config = $this->get_webhook_config($webhook_id);
        
        if (!$webhook_config) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Webhook not found'
            ), 404);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Webhook is active and ready',
            'webhook_id' => $webhook_id,
            'tag_name' => $webhook_config['tag_name'],
            'endpoint_url' => $this->get_webhook_url($webhook_id)
        ), 200);
    }
    
    /**
     * Extract contact identifier from payload
     */
    private function extract_contact_identifier($payload, $webhook_config) {
        $identifier_field = $webhook_config['identifier_field'] ?? 'email';
        
        // Support nested field access (e.g., 'user.email')
        if (strpos($identifier_field, '.') !== false) {
            $fields = explode('.', $identifier_field);
            $value = $payload;
            
            foreach ($fields as $field) {
                if (isset($value[$field])) {
                    $value = $value[$field];
                } else {
                    return null;
                }
            }
            
            return $value;
        }
        
        return isset($payload[$identifier_field]) ? $payload[$identifier_field] : null;
    }
    
    /**
     * Look up contact in FluentCRM
     */
    private function lookup_fluentcrm_contact($identifier) {
        // Use FluentCRM's official Contact API
        if (function_exists('FluentCrmApi')) {
            $contactApi = FluentCrmApi('contacts');
            return $contactApi->getContact($identifier);
        }
        
        return null;
    }
    
    /**
     * Create contact in FluentCRM
     */
    private function create_fluentcrm_contact($payload, $contact_identifier) {
        // Use FluentCRM's official Contact API
        if (function_exists('FluentCrmApi')) {
            $contactApi = FluentCrmApi('contacts');
            
            $data = array(
                'email' => $payload['email'] ?? $contact_identifier,
                'first_name' => $payload['first_name'] ?? '',
                'last_name' => $payload['last_name'] ?? '',
                'status' => 'subscribed'
            );
            
            // Add custom fields if provided
            if (isset($payload['keap_id'])) {
                $data['custom_values'] = array(
                    'keap_id' => $payload['keap_id']
                );
            }
            
            return $contactApi->createOrUpdate($data);
        }
        
        return null;
    }
    
    /**
     * Update contact custom fields
     */
    private function update_contact_custom_fields($contact, $payload) {
        if (!isset($payload['keap_id'])) {
            return;
        }
        
        // Use FluentCRM's official Contact API to update custom fields
        if (function_exists('FluentCrmApi')) {
            $contactApi = FluentCrmApi('contacts');
            
            $data = array(
                'email' => $contact->email,
                'custom_values' => array(
                    'keap_id' => $payload['keap_id']
                )
            );
            
            $contactApi->createOrUpdate($data);
        }
    }
    
    /**
     * Apply tag to contact
     */
    private function apply_tag_to_contact($contact, $tag_id) {
        // Use FluentCRM's official Contact API
        if ($contact && method_exists($contact, 'attachTags')) {
            return $contact->attachTags(array($tag_id));
        }
        
        return false;
    }
    
    /**
     * Get webhook configuration
     */
    private function get_webhook_config($webhook_id) {
        $webhooks = get_option('fluent_support_ai_webhooks', array());
        return isset($webhooks[$webhook_id]) ? $webhooks[$webhook_id] : null;
    }
    
    /**
     * Get webhook URL
     */
    private function get_webhook_url($webhook_id) {
        return rest_url('fluent-support-ai/v1/webhook/' . $webhook_id);
    }
    
    /**
     * Log webhook request
     */
    private function log_webhook_request($webhook_id, $data, $status, $additional_info = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'webhook_id' => $webhook_id,
            'status' => $status,
            'data' => $data,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'headers' => $this->get_request_headers(),
            'additional_info' => $additional_info
        );
        
        $logs = get_option('fluent_support_ai_webhook_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 200 log entries
        if (count($logs) > 200) {
            $logs = array_slice($logs, -200);
        }
        
        update_option('fluent_support_ai_webhook_logs', $logs);
    }
    
    /**
     * Get request headers
     */
    private function get_request_headers() {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_name = str_replace('HTTP_', '', $key);
                $header_name = str_replace('_', '-', $header_name);
                $header_name = ucwords(strtolower($header_name), '-');
                $headers[$header_name] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Add webhook admin menu
     */
    public function add_webhook_admin_menu() {
        add_submenu_page(
            'fluent-support-ai-settings',
            'Webhook Settings',
            'Webhooks',
            'manage_options',
            'fluent-support-ai-webhooks',
            array($this, 'webhook_settings_page')
        );
    }
    
    /**
     * Handle webhook settings form submission
     */
    public function handle_webhook_settings() {
        if (isset($_POST['action']) && $_POST['action'] === 'save_webhook' && check_admin_referer('fluent_support_ai_webhook_settings', 'fluent_support_ai_webhook_nonce')) {
            $webhook_id = sanitize_text_field($_POST['webhook_id']);
            $webhook_name = sanitize_text_field($_POST['webhook_name']);
            $tag_id = intval($_POST['tag_id']);
            $tag_name = sanitize_text_field($_POST['tag_name']);
            $identifier_field = sanitize_text_field($_POST['identifier_field']);
            $secret = sanitize_text_field($_POST['secret']);
            $active = isset($_POST['active']) ? 1 : 0;
            
            $webhooks = get_option('fluent_support_ai_webhooks', array());
            
            $webhooks[$webhook_id] = array(
                'name' => $webhook_name,
                'tag_id' => $tag_id,
                'tag_name' => $tag_name,
                'identifier_field' => $identifier_field,
                'secret' => $secret,
                'active' => $active,
                'created_at' => current_time('mysql')
            );
            
            update_option('fluent_support_ai_webhooks', $webhooks);
            
            echo '<div class="notice notice-success"><p>Webhook saved successfully!</p></div>';
        }
        
        if (isset($_POST['action']) && $_POST['action'] === 'delete_webhook' && check_admin_referer('fluent_support_ai_webhook_settings', 'fluent_support_ai_webhook_nonce')) {
            $webhook_id = sanitize_text_field($_POST['webhook_id']);
            $webhooks = get_option('fluent_support_ai_webhooks', array());
            
            if (isset($webhooks[$webhook_id])) {
                unset($webhooks[$webhook_id]);
                update_option('fluent_support_ai_webhooks', $webhooks);
                echo '<div class="notice notice-success"><p>Webhook deleted successfully!</p></div>';
            }
        }
        
        if (isset($_POST['action']) && $_POST['action'] === 'clear_webhook_logs' && check_admin_referer('fluent_support_ai_webhook_settings', 'fluent_support_ai_webhook_nonce')) {
            update_option('fluent_support_ai_webhook_logs', array());
            echo '<div class="notice notice-success"><p>Webhook logs cleared successfully!</p></div>';
        }
    }
    
    /**
     * Webhook settings page
     */
    public function webhook_settings_page() {
        $webhooks = get_option('fluent_support_ai_webhooks', array());
        $logs = get_option('fluent_support_ai_webhook_logs', array());
        
        ?>
        <div class="wrap">
            <h1>Webhook Settings</h1>
            
            <div class="fs-ai-webhook-card">
                <h2>Add New Webhook</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('fluent_support_ai_webhook_settings', 'fluent_support_ai_webhook_nonce'); ?>
                    <input type="hidden" name="action" value="save_webhook">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Webhook Name</th>
                            <td>
                                <input type="text" name="webhook_name" id="webhook_name" class="regular-text" required>
                                <p class="description">Display name for this webhook</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Webhook ID</th>
                            <td>
                                <input type="text" name="webhook_id" id="webhook_id" class="regular-text" required>
                                <p class="description">Unique identifier for this webhook (auto-generated from name)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">FluentCRM Tag ID</th>
                            <td>
                                <input type="number" name="tag_id" class="small-text" required>
                                <p class="description">The FluentCRM tag ID to apply</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Tag Name</th>
                            <td>
                                <input type="text" name="tag_name" class="regular-text" required>
                                <p class="description">Display name of the tag</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Identifier Field</th>
                            <td>
                                <input type="text" name="identifier_field" class="regular-text" value="email" required>
                                <p class="description">Field in webhook payload to identify contact (e.g., "email", "user.email")</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Secret (Optional)</th>
                            <td>
                                <input type="text" name="secret" class="regular-text">
                                <p class="description">Optional secret for webhook verification</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Active</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="active" value="1" checked>
                                    Enable this webhook
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Webhook'); ?>
                </form>
            </div>
            
            <div class="fs-ai-webhook-card">
                <h2>Existing Webhooks</h2>
                <?php if (empty($webhooks)): ?>
                    <p>No webhooks configured.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Webhook ID</th>
                                <th>Tag</th>
                                <th>Status</th>
                                <th>Endpoint URL</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($webhooks as $webhook_id => $webhook): ?>
                                <tr>
                                    <td><?php echo esc_html($webhook['name']); ?></td>
                                    <td><?php echo esc_html($webhook_id); ?></td>
                                    <td><?php echo esc_html($webhook['tag_name']); ?></td>
                                    <td>
                                        <span class="status-<?php echo $webhook['active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $webhook['active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?php echo esc_html($this->get_webhook_url($webhook_id)); ?></code>
                                    </td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field('fluent_support_ai_webhook_settings', 'fluent_support_ai_webhook_nonce'); ?>
                                            <input type="hidden" name="action" value="delete_webhook">
                                            <input type="hidden" name="webhook_id" value="<?php echo esc_attr($webhook_id); ?>">
                                            <input type="submit" class="button button-small" value="Delete" onclick="return confirm('Are you sure you want to delete this webhook?');">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="fs-ai-webhook-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Recent Webhook Logs</h2>
                    <?php if (!empty($logs)): ?>
                        <form method="post" style="display: inline;">
                            <?php wp_nonce_field('fluent_support_ai_webhook_settings', 'fluent_support_ai_webhook_nonce'); ?>
                            <input type="hidden" name="action" value="clear_webhook_logs">
                            <input type="submit" class="button button-secondary" value="Clear All Logs" onclick="return confirm('Are you sure you want to clear all webhook logs? This action cannot be undone.');">
                        </form>
                    <?php endif; ?>
                </div>
                <?php if (empty($logs)): ?>
                    <p>No webhook activity yet.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Webhook ID</th>
                                <th>Status</th>
                                <th>Step</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse(array_slice($logs, -20)) as $index => $log): ?>
                                <tr>
                                    <td><?php echo esc_html($log['timestamp']); ?></td>
                                    <td><?php echo esc_html($log['webhook_id']); ?></td>
                                    <td>
                                        <span class="status-<?php echo esc_attr($log['status']); ?>">
                                            <?php echo esc_html(ucfirst($log['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (isset($log['additional_info']['step'])): ?>
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $log['additional_info']['step']))); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['status'] === 'success'): ?>
                                            <strong>Contact:</strong> <?php echo esc_html($log['data']['contact_email']); ?><br>
                                            <strong>Tag:</strong> <?php echo esc_html($log['data']['tag_name']); ?><br>
                                            <strong>Contact ID:</strong> <?php echo esc_html($log['data']['contact_id']); ?>
                                        <?php elseif ($log['status'] === 'error'): ?>
                                            <?php 
                                            $error_text = $log['data']['error'] ?? 'Unknown error';
                                            $truncated_error = strlen($error_text) > 100 ? substr($error_text, 0, 100) . '...' : $error_text;
                                            ?>
                                            <strong>Error:</strong> <?php echo esc_html($truncated_error); ?><br>
                                            <?php if (isset($log['data']['identifier_field'])): ?>
                                                <strong>Looking for:</strong> <?php echo esc_html($log['data']['identifier_field']); ?><br>
                                            <?php endif; ?>
                                            <?php if (isset($log['data']['payload_keys'])): ?>
                                                <strong>Payload keys:</strong> <?php echo esc_html(implode(', ', $log['data']['payload_keys'])); ?><br>
                                            <?php endif; ?>
                                            <?php if (isset($log['data']['contact_identifier'])): ?>
                                                <strong>Identifier:</strong> <?php echo esc_html($log['data']['contact_identifier']); ?><br>
                                            <?php endif; ?>
                                        <?php elseif ($log['status'] === 'received'): ?>
                                            <strong>Payload size:</strong> <?php echo esc_html($log['additional_info']['payload_size'] ?? 'N/A'); ?> bytes<br>
                                            <strong>IP:</strong> <?php echo esc_html($log['ip']); ?><br>
                                            <strong>Method:</strong> <?php echo esc_html($log['method']); ?>
                                        <?php else: ?>
                                            <?php 
                                            $data_text = json_encode($log['data']);
                                            $truncated_data = strlen($data_text) > 100 ? substr($data_text, 0, 100) . '...' : $data_text;
                                            echo esc_html($truncated_data); 
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="button button-small view-log-details" data-log-index="<?php echo $index; ?>">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Log Details Modal -->
                    <div id="log-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; max-width: 80%; max-height: 80%; overflow: auto;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin: 0;">Log Details</h3>
                                <button class="button button-primary" id="copy-log-details" style="margin-left: 10px;">📋 Copy</button>
                            </div>
                            <pre id="log-details-content" style="background: #f1f1f1; padding: 15px; border-radius: 3px; overflow: auto; max-height: 400px; white-space: pre-wrap; word-wrap: break-word;"></pre>
                            <div style="margin-top: 15px; text-align: right;">
                                <span id="copy-success-message" style="color: #28a745; font-weight: bold; display: none; margin-right: 10px;">✅ Copied to clipboard!</span>
                                <button class="button" onclick="document.getElementById('log-details-modal').style.display='none';">Close</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .status-active { color: #28a745; font-weight: bold; }
        .status-inactive { color: #dc3545; font-weight: bold; }
        .status-success { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-received { color: #007cba; font-weight: bold; }
        
        /* Make layout wider and cleaner */
        .wrap { max-width: 1600px; }
        .form-table th { width: 200px; }
        .form-table td input[type="text"], 
        .form-table td input[type="number"] { width: 600px; }
        
        /* Fix table layout */
        .wp-list-table { 
            width: 100%; 
            table-layout: fixed;
        }
        .wp-list-table th, .wp-list-table td { 
            padding: 12px 8px; 
            vertical-align: top;
            word-wrap: break-word;
        }
        
        /* Column widths - much wider */
        .wp-list-table th:nth-child(1), .wp-list-table td:nth-child(1) { width: 18%; } /* Name */
        .wp-list-table th:nth-child(2), .wp-list-table td:nth-child(2) { width: 15%; } /* Webhook ID */
        .wp-list-table th:nth-child(3), .wp-list-table td:nth-child(3) { width: 15%; } /* Tag */
        .wp-list-table th:nth-child(4), .wp-list-table td:nth-child(4) { width: 6%; } /* Status */
        .wp-list-table th:nth-child(5), .wp-list-table td:nth-child(5) { width: 35%; } /* Endpoint URL */
        .wp-list-table th:nth-child(6), .wp-list-table td:nth-child(6) { width: 11%; } /* Actions */
        
        /* URL styling - allow wrapping */
        .wp-list-table code { 
            font-size: 12px;
            background: #f1f1f1;
            padding: 4px 6px;
            border-radius: 3px;
            display: block;
            max-width: 100%;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.4;
        }
        
        /* Custom webhook card styling - much wider */
        .fs-ai-webhook-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            width: 100%;
            max-width: none;
        }
        
        .fs-ai-webhook-card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        /* Form improvements */
        .form-table {
            margin-top: 0;
        }
        
        .form-table th {
            font-weight: 600;
        }
        
        .form-table .description {
            margin-top: 5px;
            font-style: italic;
            color: #666;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Auto-generate Webhook ID from Name
            $('#webhook_name').on('blur', function() {
                var name = $(this).val();
                var webhookId = $('#webhook_id');
                
                if (name && !webhookId.val()) {
                    // Convert to lowercase, replace spaces and special chars with hyphens
                    var generatedId = name.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '') // Remove special chars except spaces and hyphens
                        .replace(/\s+/g, '-') // Replace spaces with hyphens
                        .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
                        .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
                    
                    webhookId.val(generatedId);
                }
            });
            
            // View log details
            $('.view-log-details').on('click', function() {
                var logIndex = $(this).data('log-index');
                var logs = <?php echo json_encode(array_reverse(array_slice($logs, -20))); ?>;
                var log = logs[logIndex];
                
                var details = {
                    'Timestamp': log.timestamp,
                    'Webhook ID': log.webhook_id,
                    'Status': log.status,
                    'IP Address': log.ip,
                    'User Agent': log.user_agent,
                    'Method': log.method,
                    'Headers': log.headers,
                    'Data': log.data,
                    'Additional Info': log.additional_info
                };
                
                var detailsText = JSON.stringify(details, null, 2);
                $('#log-details-content').text(detailsText);
                $('#log-details-modal').show();
                
                // Store the details text for copying
                $('#log-details-modal').data('details-text', detailsText);
            });
            
            // Copy log details
            $('#copy-log-details').on('click', function() {
                var detailsText = $('#log-details-modal').data('details-text');
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(detailsText).then(function() {
                        showCopySuccess();
                    }).catch(function(err) {
                        fallbackCopyToClipboard(detailsText);
                    });
                } else {
                    fallbackCopyToClipboard(detailsText);
                }
            });
            
            function fallbackCopyToClipboard(text) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    showCopySuccess();
                } catch (err) {
                    console.error('Failed to copy text: ', err);
                    alert('Failed to copy. Please copy manually.');
                }
                
                document.body.removeChild(textArea);
            }
            
            function showCopySuccess() {
                $('#copy-success-message').show();
                setTimeout(function() {
                    $('#copy-success-message').fadeOut();
                }, 2000);
            }
            
            // Close modal when clicking outside
            $('#log-details-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        });
        </script>
        <?php
    }
}

// Initialize the webhook handler
new FluentSupportAI_Webhook_Handler();
