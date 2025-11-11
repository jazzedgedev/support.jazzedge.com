<?php
/**
 * Forms Email Sender for Katahdin AI Forms
 * Handles sending analysis emails with per-prompt settings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Forms_Email_Sender {
    
    /**
     * Send analysis email
     */
    public function send_analysis_email($form_data, $analysis, $prompt_id = null, $entry_id = null, $prompt_title = null, $prompt_text = null, $email_address = null, $email_subject = null) {
        try {
            // Validate email address
            if (empty($email_address) || !is_email($email_address)) {
                return new WP_Error('invalid_email', 'Invalid email address provided');
            }
            
            // Sanitize inputs
            $to_email = sanitize_email($email_address);
            $subject = sanitize_text_field($email_subject ?: 'AI Analysis Results - Form Submission');
            
            // Prepare email content
            $email_content = $this->prepare_email_content($form_data, $analysis, $prompt_id, $entry_id, $prompt_title, $prompt_text);
            
            // Set email headers
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            );
            
            // Send email
            $result = wp_mail($to_email, $subject, $email_content, $headers);
            
            if (!$result) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Katahdin AI Forms: Email sending failed for prompt_id: ' . $prompt_id);
                }
                return new WP_Error('email_failed', 'Failed to send email');
            }
            
            return true;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms email error: ' . $e->getMessage());
            }
            return new WP_Error('email_error', 'Email error: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare email content
     */
    private function prepare_email_content($form_data, $analysis, $prompt_id, $entry_id, $prompt_title = null, $prompt_text = null) {
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        $timestamp = current_time('mysql');
        
        // Start HTML email
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Analysis Results</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #0073aa; color: white; padding: 20px; margin: -30px -30px 30px -30px; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .section { margin-bottom: 25px; }
        .section h2 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 5px; margin-bottom: 15px; }
        .form-data { background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa; }
        .analysis { background: #e8f4fd; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa; }
        .metadata { background: #f0f0f0; padding: 10px; border-radius: 5px; font-size: 14px; color: #666; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center; }
        .label { font-weight: bold; color: #555; }
        .value { margin-bottom: 8px; }
        .prompt-info { background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AI Analysis Results</h1>
            <p>Form submission analysis from ' . esc_html($site_name) . '</p>
        </div>';
        
        // Add prompt information if available
        if ($prompt_title || $prompt_id) {
            $html .= '<div class="prompt-info">
                <strong>Analysis Prompt:</strong><br>';
            if ($prompt_title) {
                $html .= '<strong>Title:</strong> ' . esc_html($prompt_title) . '<br>';
            }
            if ($prompt_id) {
                $html .= '<strong>Prompt ID:</strong> ' . esc_html($prompt_id) . '<br>';
            }
            $html .= '</div>';
        }
        
        // Form data section
        $html .= '<div class="section">
            <h2>Form Submission Data</h2>
            <div class="form-data">';
        
        if (is_array($form_data)) {
            foreach ($form_data as $key => $value) {
                if (is_array($value)) {
                    $value = wp_json_encode($value);
                }
                $html .= '<div class="value">
                    <span class="label">' . esc_html(ucfirst(str_replace('_', ' ', $key))) . ':</span><br>
                    ' . esc_html($value) . '
                </div>';
            }
        } else {
            $html .= '<div class="value">' . esc_html($form_data) . '</div>';
        }
        
        $html .= '</div></div>';
        
        // AI Analysis section
        $html .= '<div class="section">
            <h2>AI Analysis</h2>
            <div class="analysis">' . wp_kses_post(nl2br($analysis)) . '</div>
        </div>';
        
        // Metadata section
        $html .= '<div class="section">
            <h2>Submission Details</h2>
            <div class="metadata">';
        
        if ($prompt_id) {
            $html .= '<strong>Prompt ID:</strong> ' . esc_html($prompt_id) . '<br>';
        }
        if ($entry_id) {
            $html .= '<strong>Entry ID:</strong> ' . esc_html($entry_id) . '<br>';
        }
        
        $html .= '<strong>Submitted:</strong> ' . esc_html($timestamp) . '<br>
                <strong>Site:</strong> ' . esc_html($site_name) . '<br>
                <strong>URL:</strong> <a href="' . esc_url($site_url) . '">' . esc_html($site_url) . '</a>
            </div>
        </div>';
        
        // Footer
        $html .= '<div class="footer">
            <p>This email was generated by Katahdin AI Forms plugin.</p>
            <p>Powered by <a href="https://katahdin.ai">Katahdin AI</a></p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Send test email
     */
    public function send_test_email($to_email = null) {
        try {
            $to_email = $to_email ?: get_option('admin_email');
            
            if (!is_email($to_email)) {
                return new WP_Error('invalid_email', 'Invalid email address');
            }
            
            $subject = 'Katahdin AI Forms - Test Email';
            
            $test_data = array(
                'name' => 'Test User',
                'email' => $to_email,
                'message' => 'This is a test form submission to verify email functionality.',
                'test_field' => 'Test Value'
            );
            
            $test_analysis = 'This is a test AI analysis. The email system is working correctly. You should see this analysis formatted nicely in the email.';
            
            $email_content = $this->prepare_email_content($test_data, $test_analysis, 'test_prompt', 'test_entry_123', 'Test Prompt', 'This is a test prompt for email verification.');
            
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            );
            
            $result = wp_mail($to_email, $subject, $email_content, $headers);
            
            if (!$result) {
                return new WP_Error('email_failed', 'Failed to send test email');
            }
            
            return true;
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms test email error: ' . $e->getMessage());
            }
            return new WP_Error('email_error', 'Test email error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get email template preview
     */
    public function get_email_preview($form_data = null, $analysis = null) {
        $form_data = $form_data ?: array(
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a sample form submission for preview purposes.',
            'phone' => '+1-555-123-4567'
        );
        
        $analysis = $analysis ?: 'This is a sample AI analysis that would be generated for the form submission. The analysis provides insights, recommendations, or summaries based on the form data submitted.';
        
        return $this->prepare_email_content($form_data, $analysis, 'preview_prompt', 'preview_entry', 'Preview Prompt', 'This is a preview prompt for email template testing.');
    }
    
    /**
     * Validate email settings
     */
    public function validate_email_settings($email_address, $email_subject) {
        $errors = array();
        
        if (empty($email_address)) {
            $errors[] = 'Email address is required';
        } elseif (!is_email($email_address)) {
            $errors[] = 'Invalid email address format';
        }
        
        if (empty($email_subject)) {
            $errors[] = 'Email subject is required';
        } elseif (strlen($email_subject) > 255) {
            $errors[] = 'Email subject is too long (max 255 characters)';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Get email statistics
     */
    public function get_email_stats() {
        global $wpdb;
        
        try {
            $table_name = $wpdb->prefix . 'katahdin_ai_forms_logs';
            
            $stats = $wpdb->get_row(
                "SELECT 
                    COUNT(*) as total_logs,
                    SUM(email_sent) as emails_sent,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_logs
                FROM {$table_name}",
                ARRAY_A
            );
            
            if ($stats) {
                $success_rate = $stats['successful_logs'] > 0 
                    ? round(($stats['emails_sent'] / $stats['successful_logs']) * 100, 2)
                    : 0;
                
                return array(
                    'total_logs' => intval($stats['total_logs']),
                    'emails_sent' => intval($stats['emails_sent']),
                    'successful_logs' => intval($stats['successful_logs']),
                    'email_success_rate' => $success_rate
                );
            }
            
            return array(
                'total_logs' => 0,
                'emails_sent' => 0,
                'successful_logs' => 0,
                'email_success_rate' => 0
            );
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Katahdin AI Forms email stats error: ' . $e->getMessage());
            }
            return array();
        }
    }
}
