<?php
/**
 * Reports management class
 * 
 * @package Keap_Reports
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Keap_Reports_Reports {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * API instance
     */
    private $api;
    
    /**
     * Constructor
     * 
     * @param Keap_Reports_Database $database
     * @param Keap_Reports_API $api
     */
    public function __construct($database, $api) {
        $this->database = $database;
        $this->api = $api;
    }
    
    /**
     * Fetch report data from Keap and save to database
     * 
     * @param int $report_id Report ID from database
     * @return array Array with 'success' and 'message' keys
     */
    public function fetch_report($report_id) {
        // Get report details
        $report = $this->database->get_report($report_id);
        
        if (!$report) {
            return array(
                'success' => false,
                'message' => 'Report not found'
            );
        }
        
        // Fetch data from Keap API (pass report type for better querying)
        $data = $this->api->fetch_report_data($report['report_id'], $report['report_uuid'], $report['report_type']);
        
        if (is_wp_error($data)) {
            return array(
                'success' => false,
                'message' => 'Failed to fetch data: ' . $data->get_error_message()
            );
        }
        
        // Log what we received for debugging
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        if ($debug_enabled) {
            $this->database->add_log('Fetched data for report "' . $report['name'] . '"', 'info', array(
                'report_id' => $report_id,
                'data_type' => gettype($data),
                'data_count' => is_array($data) ? count($data) : 'N/A'
            ));
            
            if (is_array($data) && !empty($data) && isset($data[0])) {
                $this->database->add_log('First record sample for report "' . $report['name'] . '"', 'debug', array(
                    'report_id' => $report_id,
                    'first_record_keys' => array_keys($data[0]),
                    'first_record' => $data[0]
                ));
            }
        }
        
        // Aggregate data based on report type
        $aggregated_value = $this->api->aggregate_data($data, $report['report_type']);
        
        // Get metadata
        $metadata = $this->api->get_metadata($data);
        
        // Get current month/year
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        
        // Save to database
        $saved = $this->database->save_report_data(
            $report_id,
            $current_year,
            $current_month,
            $aggregated_value,
            $metadata
        );
        
        if (!$saved) {
            return array(
                'success' => false,
                'message' => 'Failed to save report data to database'
            );
        }
        
        return array(
            'success' => true,
            'message' => sprintf(
                'Report fetched successfully. Value: %s',
                $this->format_value($aggregated_value, $report['report_type'])
            ),
            'value' => $aggregated_value
        );
    }
    
    /**
     * Fetch all active reports
     * 
     * @return array Array of results
     */
    public function fetch_all_active_reports() {
        $reports = $this->database->get_reports(true); // Active only
        $results = array();
        
        foreach ($reports as $report) {
            $result = $this->fetch_report($report['id']);
            $results[] = array(
                'report_id' => $report['id'],
                'report_name' => $report['name'],
                'result' => $result
            );
        }
        
        return $results;
    }
    
    /**
     * Get all reports data for a period
     * 
     * @param int $year Year
     * @param int $month Month (1-12)
     * @return array
     */
    public function get_all_reports_data($year = null, $month = null) {
        if ($year === null) {
            $year = intval(date('Y'));
        }
        if ($month === null) {
            $month = intval(date('n'));
        }
        
        return $this->database->get_all_reports_data($year, $month);
    }
    
    /**
     * Format value for display
     * 
     * @param float $value
     * @param string $report_type
     * @return string
     */
    public function format_value($value, $report_type = 'custom') {
        switch ($report_type) {
            case 'sales':
                return '$' . number_format($value, 2);
                
            case 'memberships':
            case 'subscriptions':
                return number_format($value, 0);
                
            case 'custom':
            default:
                return number_format($value, 2);
        }
    }
    
    /**
     * Get monthly comparison for a report
     * 
     * @param int $report_id
     * @param int $year Optional year (defaults to current)
     * @param int $month Optional month (defaults to current)
     * @return array
     */
    public function get_monthly_comparison($report_id, $year = null, $month = null) {
        if ($year === null) {
            $year = intval(date('Y'));
        }
        if ($month === null) {
            $month = intval(date('n'));
        }
        
        return $this->database->get_monthly_comparison($report_id, $year, $month);
    }
    
    /**
     * Get report history
     * 
     * @param int $report_id
     * @param int $limit Number of months to retrieve
     * @return array
     */
    public function get_report_history($report_id, $limit = 12) {
        return $this->database->get_report_history($report_id, $limit);
    }
}

