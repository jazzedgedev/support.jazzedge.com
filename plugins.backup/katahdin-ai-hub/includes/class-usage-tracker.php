<?php
/**
 * Usage Tracker for Katahdin AI Hub
 * Tracks API usage, costs, and performance metrics
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Katahdin_AI_Hub_Usage_Tracker {
    
    /**
     * Initialize Usage Tracker
     */
    public function init() {
        // Schedule monthly quota reset
        if (!wp_next_scheduled('katahdin_ai_hub_reset_quotas')) {
            wp_schedule_event(time(), 'monthly', 'katahdin_ai_hub_reset_quotas');
        }
        
        add_action('katahdin_ai_hub_reset_quotas', array($this, 'reset_monthly_quotas'));
    }
    
    /**
     * Log a message
     */
    public function log($level, $message, $context = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'katahdin_ai_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'plugin_id' => $context['plugin_id'] ?? 'system',
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get plugin usage statistics
     */
    public function get_plugin_stats($plugin_id, $days = 30) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $date_from = date('Y-m-d H:i:s', time() - ($days * 24 * 60 * 60));
        
        // Get overall stats
        $overall_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests,
                COUNT(*) - SUM(success) as failed_requests
             FROM $usage_table 
             WHERE plugin_id = %s AND created_at > %s",
            $plugin_id, $date_from
        ));
        
        // Get daily breakdown
        $daily_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as requests,
                SUM(tokens_used) as tokens,
                SUM(cost) as cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests
             FROM $usage_table 
             WHERE plugin_id = %s AND created_at > %s
             GROUP BY DATE(created_at)
             ORDER BY date DESC",
            $plugin_id, $date_from
        ));
        
        // Get endpoint breakdown
        $endpoint_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                endpoint,
                COUNT(*) as requests,
                SUM(tokens_used) as tokens,
                SUM(cost) as cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests
             FROM $usage_table 
             WHERE plugin_id = %s AND created_at > %s
             GROUP BY endpoint
             ORDER BY requests DESC",
            $plugin_id, $date_from
        ));
        
        // Get error breakdown
        $error_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                error_message,
                COUNT(*) as count
             FROM $usage_table 
             WHERE plugin_id = %s AND created_at > %s AND success = 0
             GROUP BY error_message
             ORDER BY count DESC",
            $plugin_id, $date_from
        ));
        
        return array(
            'overall' => $overall_stats,
            'daily' => $daily_stats,
            'endpoints' => $endpoint_stats,
            'errors' => $error_stats,
            'period_days' => $days
        );
    }
    
    /**
     * Get global usage statistics
     */
    public function get_global_stats($days = 30) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $plugins_table = $wpdb->prefix . 'katahdin_ai_plugins';
        $date_from = date('Y-m-d H:i:s', time() - ($days * 24 * 60 * 60));
        
        // Get overall stats
        $overall_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests,
                COUNT(*) - SUM(success) as failed_requests
             FROM $usage_table 
             WHERE created_at > %s",
            $date_from
        ));
        
        // Get plugin breakdown
        $plugin_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                u.plugin_id,
                p.plugin_name,
                COUNT(*) as requests,
                SUM(u.tokens_used) as tokens,
                SUM(u.cost) as cost,
                AVG(u.response_time) as avg_response_time,
                SUM(u.success) as successful_requests,
                p.quota_limit,
                p.quota_used
             FROM $usage_table u
             LEFT JOIN $plugins_table p ON u.plugin_id = p.plugin_id
             WHERE u.created_at > %s
             GROUP BY u.plugin_id
             ORDER BY requests DESC",
            $date_from
        ));
        
        // Get daily breakdown
        $daily_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as requests,
                SUM(tokens_used) as tokens,
                SUM(cost) as cost,
                AVG(response_time) as avg_response_time,
                SUM(success) as successful_requests
             FROM $usage_table 
             WHERE created_at > %s
             GROUP BY DATE(created_at)
             ORDER BY date DESC",
            $date_from
        ));
        
        return array(
            'overall' => $overall_stats,
            'plugins' => $plugin_stats,
            'daily' => $daily_stats,
            'period_days' => $days
        );
    }
    
    /**
     * Get cost analysis
     */
    public function get_cost_analysis($days = 30) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $date_from = date('Y-m-d H:i:s', time() - ($days * 24 * 60 * 60));
        
        // Get total cost
        $total_cost = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cost) FROM $usage_table WHERE created_at > %s",
            $date_from
        ));
        
        // Get cost by plugin
        $cost_by_plugin = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                plugin_id,
                SUM(cost) as total_cost,
                COUNT(*) as requests,
                AVG(cost) as avg_cost_per_request
             FROM $usage_table 
             WHERE created_at > %s
             GROUP BY plugin_id
             ORDER BY total_cost DESC",
            $date_from
        ));
        
        // Get cost by endpoint
        $cost_by_endpoint = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                endpoint,
                SUM(cost) as total_cost,
                COUNT(*) as requests,
                AVG(cost) as avg_cost_per_request
             FROM $usage_table 
             WHERE created_at > %s
             GROUP BY endpoint
             ORDER BY total_cost DESC",
            $date_from
        ));
        
        // Get daily cost trend
        $daily_cost = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                SUM(cost) as daily_cost,
                COUNT(*) as requests
             FROM $usage_table 
             WHERE created_at > %s
             GROUP BY DATE(created_at)
             ORDER BY date DESC",
            $date_from
        ));
        
        return array(
            'total_cost' => $total_cost,
            'by_plugin' => $cost_by_plugin,
            'by_endpoint' => $cost_by_endpoint,
            'daily_trend' => $daily_cost,
            'period_days' => $days
        );
    }
    
    /**
     * Get performance metrics
     */
    public function get_performance_metrics($days = 30) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $date_from = date('Y-m-d H:i:s', time() - ($days * 24 * 60 * 60));
        
        // Get response time statistics
        $response_times = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time,
                STDDEV(response_time) as stddev_response_time
             FROM $usage_table 
             WHERE created_at > %s AND success = 1",
            $date_from
        ));
        
        // Get success rate
        $success_rate = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(success) as successful_requests,
                ROUND((SUM(success) / COUNT(*)) * 100, 2) as success_percentage
             FROM $usage_table 
             WHERE created_at > %s",
            $date_from
        ));
        
        // Get error analysis
        $error_analysis = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                error_message,
                COUNT(*) as count,
                ROUND((COUNT(*) / (SELECT COUNT(*) FROM $usage_table WHERE created_at > %s)) * 100, 2) as percentage
             FROM $usage_table 
             WHERE created_at > %s AND success = 0
             GROUP BY error_message
             ORDER BY count DESC",
            $date_from, $date_from
        ));
        
        return array(
            'response_times' => $response_times,
            'success_rate' => $success_rate,
            'error_analysis' => $error_analysis,
            'period_days' => $days
        );
    }
    
    /**
     * Get quota usage across all plugins
     */
    public function get_quota_usage() {
        global $wpdb;
        
        $plugins_table = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $quota_usage = $wpdb->get_results(
            "SELECT 
                plugin_id,
                plugin_name,
                quota_limit,
                quota_used,
                ROUND((quota_used / quota_limit) * 100, 2) as usage_percentage,
                quota_limit - quota_used as remaining_quota,
                last_used
             FROM $plugins_table 
             WHERE is_active = 1
             ORDER BY usage_percentage DESC"
        );
        
        return $quota_usage;
    }
    
    /**
     * Reset monthly quotas
     */
    public function reset_monthly_quotas() {
        global $wpdb;
        
        $plugins_table = $wpdb->prefix . 'katahdin_ai_plugins';
        
        $result = $wpdb->query(
            "UPDATE $plugins_table SET quota_used = 0 WHERE is_active = 1"
        );
        
        if ($result !== false) {
            $this->log('info', 'Monthly quotas reset successfully', array(
                'plugins_updated' => $result
            ));
        }
        
        return $result !== false;
    }
    
    /**
     * Clean up old logs (older than 90 days)
     */
    public function cleanup_old_logs() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'katahdin_ai_logs';
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        
        $cutoff_date = date('Y-m-d H:i:s', time() - (90 * 24 * 60 * 60));
        
        $logs_deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $logs_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        $usage_deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $usage_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        $this->log('info', 'Old logs cleaned up', array(
            'logs_deleted' => $logs_deleted,
            'usage_deleted' => $usage_deleted
        ));
        
        return array(
            'logs_deleted' => $logs_deleted,
            'usage_deleted' => $usage_deleted
        );
    }
    
    /**
     * Export usage data
     */
    public function export_usage_data($plugin_id = null, $days = 30) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'katahdin_ai_usage';
        $date_from = date('Y-m-d H:i:s', time() - ($days * 24 * 60 * 60));
        
        $where_clause = "WHERE created_at > %s";
        $params = array($date_from);
        
        if ($plugin_id) {
            $where_clause .= " AND plugin_id = %s";
            $params[] = $plugin_id;
        }
        
        $data = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $usage_table $where_clause ORDER BY created_at DESC",
            $params
        ), ARRAY_A);
        
        return $data;
    }
}
