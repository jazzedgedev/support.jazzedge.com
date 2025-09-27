<?php
/**
 * Email Sender for Katahdin AI Webhook
 * Handles sending analysis results via email
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Webhook_Email_Sender {
    
    /**
     * Initialize email sender
     */
    public function init() {
        // Initialize any required components
    }
    
    /**
     * Send analysis email
     */
    public function send_analysis_email($form_data, $analysis, $form_id = null, $entry_id = null, $prompt_title = null, $prompt_text = null) {
        try {
            // Get email settings
            $to_email = get_option('katahdin_ai_webhook_email', get_option('admin_email'));
            $subject = get_option('katahdin_ai_webhook_email_subject', 'AI Analysis Results - Form Submission');
            
            if (empty($to_email)) {
                return new WP_Error('no_email_configured', 'No email address configured');
            }
            
            // Prepare email content
            $email_content = $this->prepare_email_content($form_data, $analysis, $form_id, $entry_id, $prompt_title, $prompt_text);
            
            // Set email headers
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            );
            
            // Send email
            $sent = wp_mail($to_email, $subject, $email_content, $headers);
            
            if (!$sent) {
                return new WP_Error('email_send_failed', 'Failed to send email');
            }
            
            return array(
                'success' => true,
                'message' => 'Email sent successfully',
                'to' => $to_email,
                'subject' => $subject
            );
            
        } catch (Exception $e) {
            error_log('Katahdin AI Webhook email error: ' . $e->getMessage());
            return new WP_Error('email_error', 'Error sending email: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare email content
     */
    private function prepare_email_content($form_data, $analysis, $form_id, $entry_id, $prompt_title = null, $prompt_text = null) {
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        $timestamp = current_time('Y-m-d H:i:s');
        
        // Start HTML email
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Analysis Results</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #0073aa; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 20px; }
        .section { margin-bottom: 25px; }
        .section h2 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 5px; margin-bottom: 15px; }
        .form-data { background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa; }
        .form-field { margin-bottom: 10px; }
        .form-field strong { display: inline-block; min-width: 120px; color: #555; }
        .analysis { background: #e8f4fd; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa; }
        .footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .metadata { background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 5px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 AI Analysis Results</h1>
            <p>Form submission analysis from ' . esc_html($site_name) . '</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>📊 Form Data</h2>
                <div class="form-data">';
        
        // Add form data
        foreach ($form_data as $key => $value) {
            $clean_key = ucwords(str_replace(array('_', '-'), ' ', $key));
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $html .= '<div class="form-field">
                <strong>' . esc_html($clean_key) . ':</strong> ' . esc_html($value) . '
            </div>';
        }
        
        $html .= '</div>
            </div>
            
            <div class="section">
                <h2>🧠 AI Analysis</h2>
                <div class="analysis">
                    ' . nl2br(esc_html($analysis)) . '
                </div>
            </div>
            
            <div class="section">
                <h2>ℹ️ Metadata</h2>
                <div class="metadata">';
        
        // Add metadata
        $html .= '<strong>Timestamp:</strong> ' . esc_html($timestamp) . '<br>';
        if ($form_id) {
            $html .= '<strong>Form ID:</strong> ' . esc_html($form_id) . '<br>';
        }
        if ($entry_id) {
            $html .= '<strong>Entry ID:</strong> ' . esc_html($entry_id) . '<br>';
        }
        if ($prompt_title) {
            $html .= '<strong>Prompt Used:</strong> ' . esc_html($prompt_title) . '<br>';
        }
        $html .= '<strong>Site:</strong> ' . esc_html($site_name) . ' (' . esc_html($site_url) . ')<br>';
        $html .= '<strong>AI Model:</strong> ' . esc_html(get_option('katahdin_ai_webhook_model', 'gpt-3.5-turbo')) . '<br>';
        
        $html .= '</div>
            </div>';
        
        // Add prompt text if provided
        if ($prompt_text) {
            $html .= '
            <div class="section">
                <h2>📝 Prompt Used</h2>
                <div class="form-data">
                    ' . nl2br(esc_html($prompt_text)) . '
                </div>
            </div>';
        }
        
        $html .= '
        </div>
        
        <div class="footer">
            <p>This email was automatically generated by Katahdin AI Webhook plugin.</p>
            <p>Powered by <a href="https://katahdin.ai" style="color: #0073aa;">Katahdin AI</a></p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Test email sending
     */
    public function test_email() {
        $test_data = array(
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'This is a test message for email functionality.',
            'form_id' => 'test_form'
        );
        
        $test_analysis = 'This is a test AI analysis result. The email system is working correctly and can send formatted analysis results to the configured email address.';
        
        return $this->send_analysis_email($test_data, $test_analysis, 'test_form', 'test_entry', 'Test Prompt', 'This is a test prompt for email functionality testing.');
    }
    
    /**
     * Get email settings
     */
    public function get_email_settings() {
        return array(
            'email' => get_option('katahdin_ai_webhook_email', ''),
            'subject' => get_option('katahdin_ai_webhook_email_subject', 'AI Analysis Results - Form Submission'),
            'admin_email' => get_option('admin_email')
        );
    }
    
    /**
     * Update email settings
     */
    public function update_email_settings($email, $subject) {
        $updated_email = update_option('katahdin_ai_webhook_email', sanitize_email($email));
        $updated_subject = update_option('katahdin_ai_webhook_email_subject', sanitize_text_field($subject));
        
        return $updated_email && $updated_subject;
    }
}
