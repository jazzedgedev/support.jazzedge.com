<?php
/**
 * REST API Handler for Keap To Fluent Tagging
 * 
 * Handles incoming POST requests from Keap to tag FluentCRM contacts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class KTF_REST_API {
    
    /**
     * Initialize REST API
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_endpoints'));
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_endpoints() {
        register_rest_route('ktf/v1', '/tag', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_tag_request'),
            'permission_callback' => '__return_true', // We'll handle auth in the callback
        ));
    }
    
    /**
     * Handle tag request from Keap
     */
    public function handle_tag_request($request) {
        // Log debug information
        $this->log_debug('=== KTF Request Received ===');
        $this->log_debug('Request Method: ' . $request->get_method());
        $this->log_debug('Request URI: ' . $request->get_route());
        
        // Log headers
        $headers = $request->get_headers();
        $this->log_debug('Headers: ' . print_r($headers, true));
        
        // Log raw body
        $raw_body = $request->get_body();
        $this->log_debug('Raw Body: ' . $raw_body);
        
        // Get JSON body
        $json_body = $request->get_json_params();
        $this->log_debug('JSON Params: ' . print_r($json_body, true));
        
        // Get body params
        $body_params = $request->get_body_params();
        $this->log_debug('Body Params: ' . print_r($body_params, true));
        
        // Get query params
        $query_params = $request->get_query_params();
        $this->log_debug('Query Params: ' . print_r($query_params, true));
        
        // Try to get body from all sources
        $body = $json_body;
        
        // If no JSON, try POST body params
        if (empty($body)) {
            $body = $body_params;
            $this->log_debug('Using body_params as body');
        } else {
            $this->log_debug('Using json_params as body');
        }
        
        // If still empty, try query params
        if (empty($body)) {
            $body = $query_params;
            $this->log_debug('Using query_params as body');
        }
        
        $this->log_debug('Final Body: ' . print_r($body, true));
        
        // Validate required fields
        if (empty($body['email'])) {
            $this->log_debug('ERROR: Email field is missing');
            return new WP_Error('missing_email', 'Email field is required', array('status' => 400));
        }
        
        if (empty($body['tag_id'])) {
            $this->log_debug('ERROR: tag_id field is missing');
            return new WP_Error('missing_tag_id', 'tag_id field is required', array('status' => 400));
        }
        
        if (empty($body['code'])) {
            $this->log_debug('ERROR: code field is missing');
            return new WP_Error('missing_code', 'code field is required', array('status' => 400));
        }
        
        $this->log_debug('Received email: ' . $body['email']);
        $this->log_debug('Received tag_id: ' . $body['tag_id']);
        $this->log_debug('Received code: ' . $body['code']);
        
        // Get tag_id2 if provided
        $tag_id2 = isset($body['tag_id2']) && !empty($body['tag_id2']) ? intval($body['tag_id2']) : null;
        if ($tag_id2 !== null) {
            $this->log_debug('Received tag_id2: ' . $tag_id2);
        }
        
        // Get action (default to 'add' if not provided)
        $action = isset($body['action']) ? strtolower(trim($body['action'])) : 'add';
        $this->log_debug('Received action: ' . $action);
        
        // Validate action
        if (!in_array($action, array('add', 'delete'))) {
            $this->log_debug('ERROR: Invalid action. Must be "add" or "delete"');
            return new WP_Error('invalid_action', 'Action must be either "add" or "delete"', array('status' => 400));
        }
        
        // Validate authentication code
        $stored_code = get_option('ktf_auth_code', '');
        $this->log_debug('Stored auth code: ' . ($stored_code ? 'SET (length: ' . strlen($stored_code) . ')' : 'NOT SET'));
        
        if (empty($stored_code)) {
            $this->log_debug('ERROR: Authentication code not configured');
            return new WP_Error('code_not_configured', 'Authentication code not configured in plugin settings', array('status' => 500));
        }
        
        if ($body['code'] !== $stored_code) {
            $this->log_debug('ERROR: Code mismatch. Received: "' . $body['code'] . '", Expected: "' . $stored_code . '"');
            return new WP_Error('invalid_code', 'Invalid authentication code', array('status' => 401));
        }
        
        $this->log_debug('Authentication code validated successfully');
        
        // Validate email format
        $email = sanitize_email($body['email']);
        if (!is_email($email)) {
            $this->log_debug('ERROR: Invalid email format: ' . $email);
            return new WP_Error('invalid_email', 'Invalid email format', array('status' => 400));
        }
        
        $this->log_debug('Email validated: ' . $email);
        
        // Validate tag_id is numeric
        $tag_id = intval($body['tag_id']);
        if ($tag_id <= 0) {
            $this->log_debug('ERROR: Invalid tag_id: ' . $body['tag_id']);
            return new WP_Error('invalid_tag_id', 'tag_id must be a positive integer', array('status' => 400));
        }
        
        $this->log_debug('Tag ID validated: ' . $tag_id);
        
        // Validate tag_id2 if provided
        if ($tag_id2 !== null && $tag_id2 <= 0) {
            $this->log_debug('ERROR: Invalid tag_id2: ' . $body['tag_id2']);
            return new WP_Error('invalid_tag_id2', 'tag_id2 must be a positive integer', array('status' => 400));
        }
        
        if ($tag_id2 !== null) {
            $this->log_debug('Tag ID2 validated: ' . $tag_id2);
        }
        
        // Check if FluentCRM is available
        if (!function_exists('FluentCrmApi')) {
            $this->log_debug('ERROR: FluentCRM API not available');
            return new WP_Error('fluentcrm_not_available', 'FluentCRM is not available', array('status' => 500));
        }
        
        $this->log_debug('FluentCRM API is available');
        
        // Get or create contact in FluentCRM
        // Create contact if: action is 'add' OR tag_id2 is provided (we need to add tag_id2)
        if ($action === 'add' || $tag_id2 !== null) {
            $this->log_debug('Attempting to get or create contact: ' . $email . ' (action: ' . $action . ', tag_id2: ' . ($tag_id2 !== null ? $tag_id2 : 'none') . ')');
            $contact = $this->get_or_create_contact($email);
        } else {
            $this->log_debug('Attempting to get contact (delete action, no tag_id2): ' . $email);
            $contact = $this->get_contact($email);
        }
        
        if (!$contact) {
            $this->log_debug('ERROR: Failed to get or create contact');
            return new WP_Error('contact_error', 'Failed to get or create contact', array('status' => 500));
        }
        
        $this->log_debug('Contact found/created. ID: ' . (isset($contact->id) ? $contact->id : 'N/A'));
        
        // Process tag_id based on action
        $tag_result = true;
        if ($tag_id > 0) {
            $this->log_debug('Attempting to ' . $action . ' tag with tag_id: ' . $tag_id);
            $tag_result = $this->tag_contact($contact, $tag_id, $action);
        }
        
        // Process tag_id2 (always add, regardless of action)
        $tag2_result = true;
        if ($tag_id2 !== null && $tag_id2 > 0) {
            $this->log_debug('Attempting to add tag_id2: ' . $tag_id2 . ' (always adds, regardless of action)');
            $tag2_result = $this->tag_contact($contact, $tag_id2, 'add');
        }
        
        if ($tag_result && $tag2_result) {
            $action_message = $action === 'add' ? 'tagged' : 'untagged';
            $messages = array();
            
            if ($tag_id > 0) {
                $messages[] = 'Contact ' . $action_message . ' with tag_id ' . $tag_id;
            }
            
            if ($tag_id2 !== null && $tag_id2 > 0) {
                $messages[] = 'Contact tagged with tag_id2 ' . $tag_id2;
            }
            
            $message = implode('. ', $messages) . '.';
            $this->log_debug('SUCCESS: ' . $message);
            $this->log_debug('=== KTF Request Completed Successfully ===');
            
            $response = array(
                'success' => true,
                'message' => $message,
                'action' => $action,
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'tag_id' => $tag_id
            );
            
            if ($tag_id2 !== null && $tag_id2 > 0) {
                $response['tag_id2'] = $tag_id2;
            }
            
            return new WP_REST_Response($response, 200);
        } else {
            $errors = array();
            if (!$tag_result) {
                $errors[] = 'Failed to ' . $action . ' tag_id ' . $tag_id;
            }
            if ($tag_id2 !== null && !$tag2_result) {
                $errors[] = 'Failed to add tag_id2 ' . $tag_id2;
            }
            
            $error_message = implode('. ', $errors);
            $this->log_debug('ERROR: ' . $error_message);
            $this->log_debug('=== KTF Request Failed ===');
            return new WP_Error('tagging_failed', $error_message, array('status' => 500));
        }
    }
    
    /**
     * Log debug information
     */
    private function log_debug($message) {
        // Check if debug logging is enabled
        $debug_enabled = get_option('ktf_debug_enabled', false);
        if (!$debug_enabled) {
            return;
        }
        
        $log_message = '[' . current_time('Y-m-d H:i:s') . '] KTF: ' . $message;
        error_log($log_message);
    }
    
    /**
     * Get or create contact in FluentCRM
     */
    private function get_or_create_contact($email) {
        if (!function_exists('FluentCrmApi')) {
            $this->log_debug('FluentCRM API function not available in get_or_create_contact');
            return null;
        }
        
        try {
            $contactApi = FluentCrmApi('contacts');
            $this->log_debug('FluentCRM Contact API initialized');
            
            // Try to get existing contact
            $this->log_debug('Attempting to get contact: ' . $email);
            $contact = $contactApi->getContact($email);
            
            if ($contact) {
                $this->log_debug('Contact found. ID: ' . (isset($contact->id) ? $contact->id : 'N/A'));
            } else {
                $this->log_debug('Contact not found, creating new contact');
                // If contact doesn't exist, create it
                $contact = $contactApi->createOrUpdate(array(
                    'email' => $email,
                    'status' => 'subscribed'
                ));
                
                if ($contact) {
                    $this->log_debug('Contact created. ID: ' . (isset($contact->id) ? $contact->id : 'N/A'));
                } else {
                    $this->log_debug('ERROR: Failed to create contact');
                }
            }
            
            return $contact;
        } catch (Exception $e) {
            $this->log_debug('EXCEPTION in get_or_create_contact: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get contact in FluentCRM (does not create)
     */
    private function get_contact($email) {
        if (!function_exists('FluentCrmApi')) {
            $this->log_debug('FluentCRM API function not available in get_contact');
            return null;
        }
        
        try {
            $contactApi = FluentCrmApi('contacts');
            $this->log_debug('FluentCRM Contact API initialized');
            
            // Get existing contact only
            $this->log_debug('Attempting to get contact: ' . $email);
            $contact = $contactApi->getContact($email);
            
            if ($contact) {
                $this->log_debug('Contact found. ID: ' . (isset($contact->id) ? $contact->id : 'N/A'));
            } else {
                $this->log_debug('Contact not found');
            }
            
            return $contact;
        } catch (Exception $e) {
            $this->log_debug('EXCEPTION in get_contact: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add or remove tag from contact in FluentCRM
     */
    private function tag_contact($contact, $tag_id, $action = 'add') {
        if (!$contact) {
            $this->log_debug('ERROR: Contact object is null in tag_contact');
            return false;
        }
        
        try {
            if ($action === 'add') {
                if (!method_exists($contact, 'attachTags')) {
                    $this->log_debug('ERROR: Contact object does not have attachTags method');
                    $this->log_debug('Contact object type: ' . get_class($contact));
                    $this->log_debug('Contact object methods: ' . print_r(get_class_methods($contact), true));
                    return false;
                }
                
                $this->log_debug('Calling attachTags with tag_id: ' . $tag_id);
                $result = $contact->attachTags(array($tag_id));
                $this->log_debug('attachTags result: ' . print_r($result, true));
            } else {
                // Delete action
                if (!method_exists($contact, 'detachTags')) {
                    $this->log_debug('ERROR: Contact object does not have detachTags method');
                    $this->log_debug('Contact object type: ' . get_class($contact));
                    $this->log_debug('Contact object methods: ' . print_r(get_class_methods($contact), true));
                    return false;
                }
                
                $this->log_debug('Calling detachTags with tag_id: ' . $tag_id);
                $result = $contact->detachTags(array($tag_id));
                $this->log_debug('detachTags result: ' . print_r($result, true));
            }
            
            return $result !== false;
        } catch (Exception $e) {
            $this->log_debug('EXCEPTION in tag_contact: ' . $e->getMessage());
            $this->log_debug('Exception trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}

