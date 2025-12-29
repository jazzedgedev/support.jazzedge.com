<?php
/**
 * Cron scheduling for Keap Reports
 * 
 * @package Keap_Reports
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Keap_Reports_Cron {
    
    /**
     * Reports instance
     */
    private $reports;
    
    /**
     * Constructor
     * 
     * @param Keap_Reports_Reports $reports
     */
    public function __construct($reports) {
        $this->reports = $reports;
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register custom cron schedules
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));
        
        // Schedule the main fetch event if not already scheduled
        add_action('admin_init', array($this, 'maybe_schedule_events'));
        
        // Hook for the scheduled fetch
        add_action('keap_reports_fetch_scheduled', array($this, 'fetch_scheduled_reports'));
    }
    
    /**
     * Add custom cron schedules
     * 
     * @param array $schedules
     * @return array
     */
    public function add_cron_schedules($schedules) {
        // Add monthly schedule if not exists
        if (!isset($schedules['monthly'])) {
            $schedules['monthly'] = array(
                'interval' => 30 * DAY_IN_SECONDS,
                'display' => __('Once Monthly', 'keap-reports')
            );
        }
        
        // Add weekly schedule if not exists
        if (!isset($schedules['weekly'])) {
            $schedules['weekly'] = array(
                'interval' => WEEK_IN_SECONDS,
                'display' => __('Once Weekly', 'keap-reports')
            );
        }
        
        return $schedules;
    }
    
    /**
     * Schedule events if not already scheduled
     */
    public function maybe_schedule_events() {
        if (!wp_next_scheduled('keap_reports_fetch_scheduled')) {
            // Get schedule frequency from settings (default: monthly)
            $frequency = get_option('keap_reports_schedule_frequency', 'monthly');
            
            // Schedule the event
            wp_schedule_event(time(), $frequency, 'keap_reports_fetch_scheduled');
        }
    }
    
    /**
     * Fetch all scheduled reports
     */
    public function fetch_scheduled_reports() {
        // Only fetch active reports
        $results = $this->reports->fetch_all_active_reports();
        
        // Log results
        foreach ($results as $result) {
            if ($result['result']['success']) {
                error_log(sprintf(
                    'Keap Reports: Successfully fetched report "%s" (ID: %d)',
                    $result['report_name'],
                    $result['report_id']
                ));
            } else {
                error_log(sprintf(
                    'Keap Reports: Failed to fetch report "%s" (ID: %d): %s',
                    $result['report_name'],
                    $result['report_id'],
                    $result['result']['message']
                ));
            }
        }
    }
    
    /**
     * Get next scheduled run time
     * 
     * @return int|false Timestamp or false if not scheduled
     */
    public function get_next_run_time() {
        return wp_next_scheduled('keap_reports_fetch_scheduled');
    }
    
    /**
     * Clear scheduled events
     */
    public function clear_scheduled_events() {
        wp_clear_scheduled_hook('keap_reports_fetch_scheduled');
    }
    
    /**
     * Reschedule events with new frequency
     * 
     * @param string $frequency Cron frequency (hourly, daily, weekly, monthly)
     */
    public function reschedule($frequency = 'monthly') {
        $this->clear_scheduled_events();
        wp_schedule_event(time(), $frequency, 'keap_reports_fetch_scheduled');
        update_option('keap_reports_schedule_frequency', $frequency);
    }
}

