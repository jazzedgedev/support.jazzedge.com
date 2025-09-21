<?php
/**
 * AI Reply Generator class for Fluent Support AI Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class FluentSupportAI_Reply_Generator {
    
    private $openai_client;
    private $prompt_manager;
    
    public function __construct() {
        $this->openai_client = new FluentSupportAI_OpenAI_Client();
        $this->prompt_manager = new FluentSupportAI_Prompt_Manager();
    }
    
    /**
     * Generate AI reply for a ticket
     */
    public function generate_reply($ticket_id, $prompt_id) {
        // Validate inputs
        if (empty($ticket_id) || empty($prompt_id)) {
            return array(
                'success' => false,
                'message' => __('Ticket ID and Prompt ID are required', 'fluent-support-ai')
            );
        }
        
        // Get ticket data
        $ticket_data = $this->get_ticket_data($ticket_id);
        if (!$ticket_data) {
            return array(
                'success' => false,
                'message' => __('Ticket not found', 'fluent-support-ai')
            );
        }
        
        // Get prompt
        $prompt = $this->prompt_manager->get_prompt_for_generation($prompt_id);
        if (!$prompt) {
            return array(
                'success' => false,
                'message' => __('Prompt not found', 'fluent-support-ai')
            );
        }
        
        // Prepare ticket content
        $ticket_content = $this->prepare_ticket_content($ticket_data);
        
        // Generate AI response
        $ai_response = $this->openai_client->generate_response($prompt, $ticket_content);
        
        if (!$ai_response['success']) {
            return array(
                'success' => false,
                'message' => $ai_response['message']
            );
        }
        
        // Log the generation for analytics
        $this->log_generation($ticket_id, $prompt_id, $ai_response);
        
        return array(
            'success' => true,
            'content' => $ai_response['content'],
            'ticket_id' => $ticket_id,
            'prompt_id' => $prompt_id,
            'usage' => isset($ai_response['usage']) ? $ai_response['usage'] : null
        );
    }
    
    /**
     * Get ticket data from database
     */
    private function get_ticket_data($ticket_id) {
        global $wpdb;
        
        $ticket = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
                $ticket_id
            ),
            ARRAY_A
        );
        
        if (!$ticket) {
            return false;
        }
        
        // Get customer data
        $customer = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}fs_customers WHERE id = %d",
                $ticket['customer_id']
            ),
            ARRAY_A
        );
        
        // Get conversations
        $conversations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}fs_conversations WHERE ticket_id = %d ORDER BY created_at ASC",
                $ticket_id
            ),
            ARRAY_A
        );
        
        return array(
            'ticket' => $ticket,
            'customer' => $customer,
            'conversations' => $conversations
        );
    }
    
    /**
     * Prepare ticket content for AI processing
     */
    private function prepare_ticket_content($ticket_data) {
        $ticket = $ticket_data['ticket'];
        $customer = $ticket_data['customer'];
        $conversations = $ticket_data['conversations'];
        
        $content = "TICKET DETAILS:\n";
        $content .= "Title: " . $ticket['title'] . "\n";
        $content .= "Status: " . $ticket['status'] . "\n";
        $content .= "Priority: " . $ticket['priority'] . "\n";
        $content .= "Created: " . $ticket['created_at'] . "\n\n";
        
        if ($customer) {
            $content .= "CUSTOMER INFORMATION:\n";
            $content .= "Name: " . $customer['first_name'] . " " . $customer['last_name'] . "\n";
            $content .= "Email: " . $customer['email'] . "\n\n";
        }
        
        $content .= "CONVERSATION HISTORY:\n";
        
        // Add initial ticket content
        if (!empty($ticket['content'])) {
            $content .= "Initial Message:\n";
            $content .= strip_tags($ticket['content']) . "\n\n";
        }
        
        // Add conversation history
        foreach ($conversations as $conversation) {
            $sender_type = $conversation['person_type'] === 'agent' ? 'Agent' : 'Customer';
            $content .= $sender_type . " (" . $conversation['created_at'] . "):\n";
            $content .= strip_tags($conversation['content']) . "\n\n";
        }
        
        return $content;
    }
    
    /**
     * Log generation for analytics
     */
    private function log_generation($ticket_id, $prompt_id, $ai_response) {
        // Store generation log in options (you might want to use a custom table for better performance)
        $logs = get_option('fluent_support_ai_generation_logs', array());
        
        $log_entry = array(
            'ticket_id' => $ticket_id,
            'prompt_id' => $prompt_id,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'tokens_used' => isset($ai_response['usage']['total_tokens']) ? $ai_response['usage']['total_tokens'] : 0,
            'model' => $this->openai_client->get_model()
        );
        
        $logs[] = $log_entry;
        
        // Keep only last 100 logs to prevent option table bloat
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('fluent_support_ai_generation_logs', $logs);
    }
    
    /**
     * Get generation statistics
     */
    public function get_generation_stats() {
        $logs = get_option('fluent_support_ai_generation_logs', array());
        
        $stats = array(
            'total_generations' => count($logs),
            'today_generations' => 0,
            'this_week_generations' => 0,
            'total_tokens_used' => 0
        );
        
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('-7 days'));
        
        foreach ($logs as $log) {
            $log_date = date('Y-m-d', strtotime($log['timestamp']));
            
            if ($log_date === $today) {
                $stats['today_generations']++;
            }
            
            if ($log_date >= $week_start) {
                $stats['this_week_generations']++;
            }
            
            $stats['total_tokens_used'] += $log['tokens_used'];
        }
        
        return $stats;
    }
    
    /**
     * Validate ticket access
     */
    private function validate_ticket_access($ticket_id) {
        // Check if current user can access this ticket
        if (!current_user_can('fluent_support_manage_tickets')) {
            return false;
        }
        
        // Additional validation can be added here based on your requirements
        return true;
    }
    
    /**
     * Get suggested prompts based on ticket content
     */
    public function get_suggested_prompts($ticket_id) {
        $ticket_data = $this->get_ticket_data($ticket_id);
        if (!$ticket_data) {
            return array();
        }
        
        $ticket_content = strtolower($ticket_data['ticket']['content']);
        $suggestions = array();
        
        // Simple keyword-based suggestions
        $keywords = array(
            'technical' => array('error', 'bug', 'issue', 'problem', 'not working'),
            'billing' => array('payment', 'charge', 'refund', 'billing', 'invoice'),
            'account' => array('login', 'password', 'account', 'access', 'sign in'),
            'feature' => array('feature', 'request', 'enhancement', 'improvement')
        );
        
        $all_prompts = $this->prompt_manager->get_prompts();
        
        foreach ($keywords as $category => $category_keywords) {
            foreach ($category_keywords as $keyword) {
                if (strpos($ticket_content, $keyword) !== false) {
                    // Find prompts that might be relevant
                    foreach ($all_prompts as $prompt) {
                        if (strpos(strtolower($prompt['name']), $category) !== false) {
                            $suggestions[] = $prompt;
                        }
                    }
                    break;
                }
            }
        }
        
        return array_unique($suggestions, SORT_REGULAR);
    }
}
