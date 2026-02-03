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
        
        // Schedule daily subscription fetch
        add_action('admin_init', array($this, 'maybe_schedule_daily_subscriptions'));
        
        // Hook for daily subscription fetch
        add_action('keap_reports_fetch_daily_subscriptions', array($this, 'fetch_daily_subscriptions'));
        
        // Hook for catching up on missed days
        add_action('keap_reports_fetch_yesterday_subscriptions', array($this, 'fetch_yesterday_subscriptions'));
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
        // Check if auto-fetch is enabled (default to enabled)
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            // Auto-fetch is disabled, clear any existing scheduled events
            $this->clear_scheduled_events();
            return;
        }
        
        // Schedule saved reports fetch (daily at configured time)
        if (!wp_next_scheduled('keap_reports_fetch_scheduled')) {
            // Get daily fetch time from settings (default: 08:00 Eastern)
            $daily_fetch_time = get_option('keap_reports_daily_fetch_time', '08:00');
            $this->reschedule_daily($daily_fetch_time);
        }
    }
    
    /**
     * Schedule daily subscription fetch if not already scheduled
     */
    public function maybe_schedule_daily_subscriptions() {
        // Check if auto-fetch is enabled (default to enabled)
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            // Auto-fetch is disabled, clear any existing scheduled events
            $this->clear_daily_subscription_events();
            return;
        }
        
        if (!wp_next_scheduled('keap_reports_fetch_daily_subscriptions')) {
            // Schedule to run daily at 2 AM (site timezone)
            $timezone = get_option('timezone_string');
            if (empty($timezone)) {
                $timezone = 'America/New_York'; // Default fallback
            }
            
            // Get current time in site timezone
            $now = new DateTime('now', new DateTimeZone($timezone));
            $scheduled_time = clone $now;
            $scheduled_time->setTime(2, 0, 0); // 2:00 AM
            
            // If it's already past 2 AM today, schedule for tomorrow
            if ($now >= $scheduled_time) {
                $scheduled_time->modify('+1 day');
            }
            
            $timestamp = $scheduled_time->getTimestamp();
            wp_schedule_event($timestamp, 'daily', 'keap_reports_fetch_daily_subscriptions');
        }
    }
    
    /**
     * Fetch all scheduled reports
     */
    public function fetch_scheduled_reports() {
        $database = new Keap_Reports_Database();
        $start_time = time();
        
        // Check if auto-fetch is enabled
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            $database->add_log('CRON: Saved Reports Fetch - Auto-fetch disabled, skipping', 'warning');
            error_log('Keap Reports: Automatic fetching is disabled. Skipping scheduled fetch.');
            return;
        }
        
        $database->add_log('CRON: Saved Reports Fetch - Started', 'info');
        
        // Only fetch active reports
        $results = $this->reports->fetch_all_active_reports();
        
        $success_count = 0;
        $fail_count = 0;
        
        // Log results
        foreach ($results as $result) {
            if ($result['result']['success']) {
                $success_count++;
                error_log(sprintf(
                    'Keap Reports: Successfully fetched report "%s" (ID: %d)',
                    $result['report_name'],
                    $result['report_id']
                ));
            } else {
                $fail_count++;
                error_log(sprintf(
                    'Keap Reports: Failed to fetch report "%s" (ID: %d): %s',
                    $result['report_name'],
                    $result['report_id'],
                    $result['result']['message']
                ));
            }
        }
        
        $duration = time() - $start_time;
        $database->add_log(sprintf('CRON: Saved Reports Fetch - Completed in %ds (%d success, %d failed)', $duration, $success_count, $fail_count), $fail_count > 0 ? 'warning' : 'info');
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
     * Get next scheduled daily subscription fetch time
     * 
     * @return int|false Timestamp or false if not scheduled
     */
    public function get_next_daily_subscription_run_time() {
        return wp_next_scheduled('keap_reports_fetch_daily_subscriptions');
    }
    
    /**
     * Check if daily subscription cron is scheduled
     * 
     * @return bool
     */
    public function is_daily_subscription_scheduled() {
        return wp_next_scheduled('keap_reports_fetch_daily_subscriptions') !== false;
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
    
    /**
     * Reschedule daily fetch with specific time (Eastern Time)
     * 
     * @param string $time_et Time in HH:MM format (Eastern Time)
     */
    public function reschedule_daily($time_et = '08:00') {
        $this->clear_scheduled_events();
        
        // Parse time (HH:MM format)
        list($hour, $minute) = explode(':', $time_et);
        $hour = intval($hour);
        $minute = intval($minute);
        
        // Create DateTime in Eastern timezone
        $et_timezone = new DateTimeZone('America/New_York');
        $now_et = new DateTime('now', $et_timezone);
        $scheduled_et = clone $now_et;
        $scheduled_et->setTime($hour, $minute, 0);
        
        // If it's already past the scheduled time today, schedule for tomorrow
        if ($now_et >= $scheduled_et) {
            $scheduled_et->modify('+1 day');
        }
        
        // Convert to UTC timestamp for WordPress cron
        $scheduled_et->setTimezone(new DateTimeZone('UTC'));
        $timestamp = $scheduled_et->getTimestamp();
        
        // Schedule daily
        wp_schedule_event($timestamp, 'daily', 'keap_reports_fetch_scheduled');
    }
    
    /**
     * Fetch daily subscription snapshots
     */
    public function fetch_daily_subscriptions() {
        $database = new Keap_Reports_Database();
        $start_time = time();
        
        // Check if auto-fetch is enabled
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            $database->add_log('CRON: Subscription Reports Fetch - Auto-fetch disabled, skipping', 'warning');
            error_log('Keap Reports: Automatic fetching is disabled. Skipping daily subscription fetch.');
            return;
        }
        
        $database->add_log('CRON: Subscription Reports Fetch - Started', 'info');
        
        // Check if we need to catch up on missed days (yesterday)
        $database = new Keap_Reports_Database();
        $last_fetch_time = $database->get_last_daily_subscription_fetch_time();
        
        $yesterday = new DateTime('yesterday');
        $yesterday_year = intval($yesterday->format('Y'));
        $yesterday_month = intval($yesterday->format('n'));
        $yesterday_day = intval($yesterday->format('j'));
        
        // Check if yesterday's data exists
        $yesterday_data = $database->get_daily_subscription(null, $yesterday_year, $yesterday_month, $yesterday_day);
        
        // If yesterday is missing, fetch it first
        if (!$yesterday_data) {
            error_log('Keap Reports: Yesterday\'s subscription data is missing. Fetching yesterday (' . $yesterday->format('Y-m-d') . ') first.');
            
            // Set transient to override date for yesterday
            set_transient('keap_reports_fetch_date_override', array(
                'year' => $yesterday_year,
                'month' => $yesterday_month,
                'day' => $yesterday_day
            ), 300); // 5 minute expiry
            
            $yesterday_result = $this->reports->fetch_daily_subscriptions();
            delete_transient('keap_reports_fetch_date_override');
            
            if ($yesterday_result['success']) {
                error_log('Keap Reports: Yesterday\'s subscription snapshot completed. ' . $yesterday_result['message']);
            } else {
                error_log('Keap Reports: Yesterday\'s subscription snapshot failed. ' . $yesterday_result['message']);
            }
        }
        
        // Now fetch today's data
        $result = $this->reports->fetch_daily_subscriptions();
        
        $duration = time() - $start_time;
        
        if ($result['success']) {
            $database->add_log(sprintf('CRON: Subscription Reports Fetch - Completed in %ds - %s', $duration, $result['message']), 'info');
            error_log('Keap Reports: Daily subscription snapshot completed. ' . $result['message']);
        } else {
            $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
            $database->add_log(sprintf('CRON: Subscription Reports Fetch - Failed in %ds - %s', $duration, $error_msg), 'error');
            error_log('Keap Reports: Daily subscription snapshot failed. ' . $error_msg);
        }
    }
    
    /**
     * Clear daily subscription scheduled events
     */
    public function clear_daily_subscription_events() {
        wp_clear_scheduled_hook('keap_reports_fetch_daily_subscriptions');
    }
}

