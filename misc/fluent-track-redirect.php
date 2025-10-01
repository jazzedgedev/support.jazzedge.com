<?php
/**
 * FluentCRM Tag Tracking and Redirect Script
 * 
 * This script adds a tag to a FluentCRM contact and then redirects the user.
 * 
 * Usage: fluent-track-redirect.php?contact_id=1234&email=email@example.com&tag_id=1&redirect=https://example.com
 * 
 * Required parameters:
 * - contact_id: The FluentCRM contact ID
 * - email: The contact's email address (for verification)
 * - tag_id: The FluentCRM tag ID to add
 * - redirect: The URL to redirect to after processing
 */

// Prevent direct access if not through WordPress
if (!defined('ABSPATH')) {
    // Load WordPress from the main directory
    require_once('/home/customer/www/support.jazzedge.com/public_html/wp-load.php');
}

// Check if FluentCRM is active
if (!function_exists('FluentCrmApi')) {
    die('FluentCRM plugin is not active.');
}

// Get query parameters
$contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
$tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0;
$redirect_url = isset($_GET['redirect']) ? esc_url_raw($_GET['redirect']) : '';

// Validate required parameters
if (empty($contact_id) || empty($email) || empty($tag_id) || empty($redirect_url)) {
    die('Missing required parameters. Usage: fluent-track-redirect.php?contact_id=1234&email=email@example.com&tag_id=1&redirect=https://example.com');
}

// Validate email format
if (!is_email($email)) {
    die('Invalid email address format.');
}

// Validate redirect URL
if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
    die('Invalid redirect URL.');
}

try {
    // Get the contact from FluentCRM using the API
    $contactApi = FluentCrmApi('contacts');
    $contact = $contactApi->getContact($contact_id);
    
    if (!$contact) {
        die('Contact not found with ID: ' . $contact_id);
    }
    
    // Verify email matches (security check)
    if ($contact->email !== $email) {
        die('Email mismatch. Contact email does not match provided email.');
    }
    
    // Add the tag to the contact using the correct API method
    $result = $contact->attachTags([$tag_id]);
    
    if ($result) {
        // Log the action (optional)
        error_log("FluentCRM Tag Added: Contact ID {$contact_id} tagged with Tag ID {$tag_id}");
        
        // Redirect the user
        wp_redirect($redirect_url);
        exit;
    } else {
        die('Failed to add tag to contact.');
    }
    
} catch (Exception $e) {
    error_log('FluentCRM Tag Error: ' . $e->getMessage());
    die('An error occurred while processing the request: ' . $e->getMessage());
}
?>
