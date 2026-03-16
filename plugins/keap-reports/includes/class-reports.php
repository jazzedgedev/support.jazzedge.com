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
    public function fetch_report($report_id, $is_manual = false) {
        // Get report details
        $report = $this->database->get_report($report_id);
        
        if (!$report) {
            return array(
                'success' => false,
                'message' => 'Report not found'
            );
        }
        
        // Get filter_product_id if this is a subscriptions report
        $filter_product_ids = array();
        if ($report['report_type'] === 'subscriptions' && !empty($report['filter_product_id'])) {
            $filter_product_ids = array_map('intval', array_map('trim', explode(',', $report['filter_product_id'])));
        }
        
        // Fetch data from Keap API using only report_id (UUID is optional now)
        $report_uuid = !empty($report['report_uuid']) ? $report['report_uuid'] : '';
        $data = $this->api->fetch_report_data($report['report_id'], $report_uuid, $report['report_type'], $filter_product_ids, $is_manual);
        
        if (is_wp_error($data)) {
            return array(
                'success' => false,
                'message' => 'Failed to fetch data: ' . $data->get_error_message()
            );
        }
        
        // Map old report types to new types for backward compatibility
        $report_type = $report['report_type'];
        if ($report_type === 'sales') {
            $report_type = 'monthly_revenue';
        } elseif ($report_type === 'paid_starter') {
            $report_type = 'count';
        } elseif ($report_type === 'intensives') {
            $report_type = 'count_revenue';
        }
        
        // Handle count report type (old: paid_starter) - it's a snapshot, not monthly aggregation
        if ($report_type === 'count') {
            return $this->fetch_paid_starter_report($report_id, $report, $data);
        }
        
        // Handle count_revenue report type (old: intensives) - process OrderDate and OrderTotal
        if ($report_type === 'count_revenue') {
            return $this->fetch_intensives_report($report_id, $report, $data);
        }
        
        // Check if data is empty - show warning but continue
        if (empty($data) || !is_array($data) || count($data) === 0) {
            $this->database->add_log(
                'Warning: Report "' . $report['name'] . '" (ID: ' . $report['report_id'] . ') returned no results. This may be normal if the saved search has no data for the current period.',
                'warning',
                array(
                    'report_id' => $report['report_id'],
                    'report_name' => $report['name'],
                    'data_type' => gettype($data),
                    'data_count' => is_array($data) ? count($data) : 0
                )
            );
            
            // Still save empty data with metadata for tracking
            $metadata = array(
                'fetched_at' => current_time('mysql'),
                'report_id' => $report['report_id'],
                'report_type' => $report['report_type'],
                'data_type' => gettype($data),
                'data_count' => 0,
                'note' => 'No results returned from saved search'
            );
            
            $current_year = intval(date('Y'));
            $current_month = intval(date('n'));
            
            $this->database->save_report_data(
                $report_id,
                $current_year,
                $current_month,
                0,
                $metadata,
                0,
                0.00
            );
            
            return array(
                'success' => true,
                'message' => 'Report fetched but returned no results. This may be normal if the saved search has no data.',
                'value' => 0,
                'num_orders' => 0,
                'total_amt_sold' => 0.00
            );
        }
        
        // Prepare raw data dump for metadata
        $raw_data_dump = array(
            'fetched_at' => current_time('mysql'),
            'report_id' => $report['report_id'],
            'report_type' => $report['report_type'],
            'data_type' => gettype($data),
            'data_count' => is_array($data) ? count($data) : 'N/A',
            'raw_data' => $data
        );
        
        // Log what we received
        $this->database->add_log('Data fetched for report "' . $report['name'] . '"', 'info', array(
            'report_id' => $report['report_id'],
            'data_count' => is_array($data) ? count($data) : 0,
            'first_record_sample' => is_array($data) && count($data) > 0 ? $data[0] : null
        ));
        
        // Aggregate data by Year and Month
        // Expected format: Array with records containing NumOrders, AmtSold, Month, Year
        $monthly_data = array(); // year => month => array('num_orders' => X, 'total_amt_sold' => Y)
        
        // Log the structure of the first record to understand the data format
        $this->database->add_log('Starting data aggregation', 'info', array(
            'report_id' => $report['report_id'],
            'total_records' => count($data),
            'data_type' => gettype($data),
            'is_array' => is_array($data)
        ));
        
        if (count($data) > 0 && is_array($data[0])) {
            $this->database->add_log('First record structure from saved search', 'info', array(
                'report_id' => $report['report_id'],
                'first_record' => $data[0],
                'first_record_keys' => array_keys($data[0]),
                'has_Year' => isset($data[0]['Year']),
                'has_Month' => isset($data[0]['Month']),
                'Year_value' => isset($data[0]['Year']) ? $data[0]['Year'] : 'not_set',
                'Month_value' => isset($data[0]['Month']) ? $data[0]['Month'] : 'not_set'
            ));
        }
        
        $records_processed = 0;
        $records_skipped = 0;
        $skip_reasons = array();
        
        foreach ($data as $record_index => $record) {
            if (!is_array($record)) {
                $records_skipped++;
                $skip_reasons[] = "Record #{$record_index} is not an array";
                continue;
            }
            
            $records_processed++;
            
            // Extract year and month - try multiple field name variations
            $year = null;
            $month = null;
            
            // Try various field name combinations
            if (isset($record['Year'])) {
                $year = intval($record['Year']);
            } elseif (isset($record['year'])) {
                $year = intval($record['year']);
            } elseif (isset($record['YEAR'])) {
                $year = intval($record['YEAR']);
            }
            
            if (isset($record['Month'])) {
                $month = intval($record['Month']);
            } elseif (isset($record['month'])) {
                $month = intval($record['month']);
            } elseif (isset($record['MONTH'])) {
                $month = intval($record['MONTH']);
            }
            
            // Always log the first record processing
            if ($record_index === 0) {
                $this->database->add_log("Processing first record", 'info', array(
                    'record' => $record,
                    'record_keys' => array_keys($record),
                    'found_year' => $year,
                    'found_month' => $month,
                    'year_valid' => ($year && $year > 0),
                    'month_valid' => ($month && $month >= 1 && $month <= 12)
                ));
            }
            
            // Skip if we don't have valid year/month
            if (!$year || !$month || $month < 1 || $month > 12) {
                $records_skipped++;
                $skip_reasons[] = "Record #{$record_index}: year={$year}, month={$month} (invalid)";
                if ($record_index === 0) {
                    $this->database->add_log("First record skipped - invalid year/month", 'error', array(
                        'year' => $year,
                        'month' => $month,
                        'record' => $record,
                        'all_keys' => array_keys($record)
                    ));
                }
                continue;
            }
            
            // Initialize if not exists
            if (!isset($monthly_data[$year])) {
                $monthly_data[$year] = array();
            }
            if (!isset($monthly_data[$year][$month])) {
                $monthly_data[$year][$month] = array(
                    'num_orders' => 0,
                    'total_amt_sold' => 0.00
                );
            }
            
            // Sum NumOrders and AmtSold
            if (isset($record['NumOrders'])) {
                $monthly_data[$year][$month]['num_orders'] += intval($record['NumOrders']);
            } else {
                // Log if NumOrders is missing
                if ($record_index === 0) {
                    $this->database->add_log("Warning: NumOrders field not found in record", 'warning', array(
                        'record' => $record,
                        'available_keys' => array_keys($record)
                    ));
                }
            }
            
            if (isset($record['AmtSold'])) {
                $monthly_data[$year][$month]['total_amt_sold'] += floatval($record['AmtSold']);
            } else {
                // Log if AmtSold is missing
                if ($record_index === 0) {
                    $this->database->add_log("Warning: AmtSold field not found in record", 'warning', array(
                        'record' => $record,
                        'available_keys' => array_keys($record)
                    ));
                }
            }
            
            // Log successful processing of first record
            if ($record_index === 0) {
                $this->database->add_log("Successfully processed first record", 'info', array(
                    'year' => $year,
                    'month' => $month,
                    'num_orders' => isset($record['NumOrders']) ? intval($record['NumOrders']) : 0,
                    'amt_sold' => isset($record['AmtSold']) ? floatval($record['AmtSold']) : 0.00,
                    'monthly_data_so_far' => $monthly_data
                ));
            }
        }
        
        // Log aggregation results before saving
        $this->database->add_log('After aggregation loop', 'info', array(
            'monthly_data_count' => count($monthly_data),
            'monthly_data' => $monthly_data,
            'records_processed' => $records_processed,
            'records_skipped' => $records_skipped
        ));
        
        // Save each month's data
        $saved_count = 0;
        $total_orders = 0;
        $total_amt = 0.00;
        $save_errors = array();
        
        foreach ($monthly_data as $year => $months) {
            foreach ($months as $month => $month_data) {
                $metadata = $raw_data_dump;
                $metadata['year'] = $year;
                $metadata['month'] = $month;
                
                $this->database->add_log("Attempting to save report data", 'info', array(
                    'report_id' => $report_id,
                    'year' => $year,
                    'month' => $month,
                    'num_orders' => $month_data['num_orders'],
                    'total_amt_sold' => $month_data['total_amt_sold'],
                    'value' => $month_data['total_amt_sold']
                ));
                
                $saved = $this->database->save_report_data(
                    $report_id,
                    $year,
                    $month,
                    $month_data['total_amt_sold'], // Use total_amt_sold as the value
                    $metadata,
                    $month_data['num_orders'],
                    $month_data['total_amt_sold']
                );
                
                if ($saved) {
                    $saved_count++;
                    $total_orders += $month_data['num_orders'];
                    $total_amt += $month_data['total_amt_sold'];
                    $this->database->add_log("Successfully saved report data", 'info', array(
                        'report_id' => $report_id,
                        'year' => $year,
                        'month' => $month
                    ));
                } else {
                    $error_msg = "Failed to save report data for report_id={$report_id}, year={$year}, month={$month}";
                    $save_errors[] = $error_msg;
                    $this->database->add_log($error_msg, 'error', array(
                        'report_id' => $report_id,
                        'year' => $year,
                        'month' => $month,
                        'num_orders' => $month_data['num_orders'],
                        'total_amt_sold' => $month_data['total_amt_sold'],
                        'note' => 'Check PHP error log for database errors'
                    ));
                }
            }
        }
        
        // Log aggregation summary
        $this->database->add_log('Data aggregation summary', 'info', array(
            'report_id' => $report['report_id'],
            'total_records' => count($data),
            'records_processed' => $records_processed,
            'records_skipped' => $records_skipped,
            'skip_reasons' => array_slice($skip_reasons, 0, 10), // First 10 skip reasons
            'months_found' => count($monthly_data),
            'months_data' => $monthly_data,
            'saved_count' => $saved_count
        ));
        
        if ($saved_count === 0) {
            // Provide detailed error message
            $error_details = "Total records: " . count($data) . ", Processed: {$records_processed}, Skipped: {$records_skipped}";
            if (count($data) > 0 && is_array($data[0])) {
                $error_details .= ". First record keys: " . implode(', ', array_keys($data[0]));
            }
            
            return array(
                'success' => false,
                'message' => 'Failed to save report data to database. No valid year/month data found in results. ' . $error_details
            );
        }
        
        return array(
            'success' => true,
            'message' => sprintf(
                'Report fetched successfully. Saved %d month(s) of data. Total: %d orders, $%s',
                $saved_count,
                $total_orders,
                number_format($total_amt, 2)
            ),
            'months_saved' => $saved_count,
            'num_orders' => $total_orders,
            'total_amt_sold' => $total_amt
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
    
    /**
     * Fetch and store daily subscription snapshots by product
     * Uses batch processing to avoid memory issues
     * 
     * @return array Array with 'success' and 'message' keys
     */
    public function fetch_daily_subscriptions() {
        // Check if plugin is active
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
            $debug_enabled = get_option('keap_reports_debug_enabled', false);
            if ($debug_enabled) {
                $this->database->add_log('Daily subscription fetch skipped: Plugin is deactivated', 'info');
            }
            return array(
                'success' => false,
                'message' => 'Plugin is deactivated. Please activate the plugin.'
            );
        }
        
        // Check if auto-fetch is enabled
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            $debug_enabled = get_option('keap_reports_debug_enabled', false);
            if ($debug_enabled) {
                $this->database->add_log('Daily subscription fetch skipped: Auto-fetch is disabled', 'info');
            }
            return array(
                'success' => false,
                'message' => 'Automatic fetching is disabled. Please enable it in settings.'
            );
        }
        
        // Log start
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        if ($debug_enabled) {
            $this->database->add_log('Starting daily subscription fetch (batch mode)', 'info');
        }
        
        // Get API key
        $api_key = get_option('keap_reports_api_key', '');
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'API key is not configured'
            );
        }
        
        // Get current date (or override from transient for catch-up fetches)
        $date_override = get_transient('keap_reports_fetch_date_override');
        if ($date_override && is_array($date_override)) {
            $current_year = intval($date_override['year']);
            $current_month = intval($date_override['month']);
            $current_day = intval($date_override['day']);
        } else {
            $current_year = intval(date('Y'));
            $current_month = intval(date('n'));
            $current_day = intval(date('j'));
        }
        
        // Track subscriptions by product ID (incrementally)
        $subscriptions_by_product = array();
        $subscriptions_by_product_details = array(); // Store individual subscription records
        $product_id_field_names = array('product_id', 'productId', 'product', 'productid', 'product_id_number');
        $subscriptions_without_product_id = 0;
        $total_processed = 0;
        $batch_count = 0;
        
        // Log sample record on first batch
        $first_batch = true;
        
        // Define callback to process each batch
        $database = $this->database; // Capture database instance for closure
        $process_batch = function($batch) use (&$subscriptions_by_product, &$subscriptions_by_product_details, &$subscriptions_without_product_id, &$total_processed, &$batch_count, &$first_batch, $product_id_field_names, $debug_enabled, $database, $current_year, $current_month, $current_day) {
            // Check if plugin is still active
            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                if ($debug_enabled) {
                    $database->add_log('Plugin was deactivated during daily subscription fetch. Stopping batch processing.', 'warning');
                }
                return false; // Signal to stop processing
            }
            
            // Check if auto-fetch was disabled during processing
            $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
            if (!$auto_fetch_enabled) {
                if ($debug_enabled) {
                    $database->add_log('Auto-fetch was disabled during daily subscription fetch. Stopping batch processing.', 'warning');
                }
                return false; // Signal to stop processing
            }
            
            $batch_count++;
            $batch_size = count($batch);
            $total_processed += $batch_size;
            
            // Log sample record structure on first batch
            if ($first_batch && !empty($batch) && isset($batch[0])) {
                $sample_keys = array_keys($batch[0]);
                if ($debug_enabled) {
                    $database->add_log('Sample subscription record keys: ' . implode(', ', $sample_keys), 'debug', array(
                        'sample_record' => $batch[0]
                    ));
                }
                $first_batch = false;
            }
            
            // Process each subscription in the batch
            foreach ($batch as $sub) {
                if (!is_array($sub)) {
                    continue;
                }
                
                $found_product_id = null;
                
                // Try multiple possible field names for product ID
                foreach ($product_id_field_names as $field_name) {
                    if (isset($sub[$field_name])) {
                        $found_product_id = $sub[$field_name];
                        break;
                    }
                }
                
                // Also check nested structures
                if ($found_product_id === null && isset($sub['product']) && is_array($sub['product'])) {
                    foreach ($product_id_field_names as $field_name) {
                        if (isset($sub['product'][$field_name])) {
                            $found_product_id = $sub['product'][$field_name];
                            break;
                        }
                    }
                }
                
                if ($found_product_id !== null) {
                    $product_id = strval($found_product_id);
                    if (!isset($subscriptions_by_product[$product_id])) {
                        $subscriptions_by_product[$product_id] = 0;
                        $subscriptions_by_product_details[$product_id] = array();
                    }
                    $subscriptions_by_product[$product_id]++;
                    
                    // Extract contact information
                    $subscription_id = isset($sub['id']) ? strval($sub['id']) : (isset($sub['Id']) ? strval($sub['Id']) : '');
                    $contact_id = isset($sub['contact_id']) ? strval($sub['contact_id']) : (isset($sub['contactId']) ? strval($sub['contactId']) : (isset($sub['ContactId']) ? strval($sub['ContactId']) : ''));
                    
                    // Try to get contact name and email from subscription data
                    $contact_name = '';
                    $contact_email = '';
                    
                    // Check various possible field names
                    $name_fields = array('contact_name', 'contactName', 'ContactName', 'name', 'Name', 'given_name', 'first_name', 'firstName');
                    $email_fields = array('contact_email', 'contactEmail', 'ContactEmail', 'email', 'Email', 'email_address', 'emailAddress');
                    
                    foreach ($name_fields as $field) {
                        if (isset($sub[$field]) && !empty($sub[$field])) {
                            $contact_name = $sub[$field];
                            break;
                        }
                    }
                    
                    foreach ($email_fields as $field) {
                        if (isset($sub[$field]) && !empty($sub[$field])) {
                            $contact_email = $sub[$field];
                            break;
                        }
                    }
                    
                    // If contact info not in subscription, check nested contact object
                    if (empty($contact_name) && isset($sub['contact']) && is_array($sub['contact'])) {
                        foreach ($name_fields as $field) {
                            if (isset($sub['contact'][$field]) && !empty($sub['contact'][$field])) {
                                $contact_name = $sub['contact'][$field];
                                break;
                            }
                        }
                    }
                    
                    if (empty($contact_email) && isset($sub['contact']) && is_array($sub['contact'])) {
                        foreach ($email_fields as $field) {
                            if (isset($sub['contact'][$field]) && !empty($sub['contact'][$field])) {
                                $contact_email = $sub['contact'][$field];
                                break;
                            }
                        }
                    }
                    
                    // Store subscription detail
                    if (!empty($subscription_id) && !empty($contact_id)) {
                        $subscriptions_by_product_details[$product_id][] = array(
                            'subscription_id' => $subscription_id,
                            'contact_id' => $contact_id,
                            'contact_name' => $contact_name,
                            'contact_email' => $contact_email
                        );
                    }
                } else {
                    $subscriptions_without_product_id++;
                }
            }
            
            // Log progress every 10 batches
            if ($debug_enabled && $batch_count % 10 == 0) {
                $database->add_log("Processed {$batch_count} batches, {$total_processed} total subscriptions", 'info');
            }
        };
        
        // Fetch subscriptions in batches
        $result = $this->api->fetch_subscriptions_in_batches($api_key, $process_batch, 100);
        
        if (is_wp_error($result)) {
            $error_message = 'API Error: ' . $result->get_error_message();
            if ($debug_enabled) {
                $this->database->add_log('Daily subscription fetch failed: ' . $error_message, 'error', array(
                    'error_code' => $result->get_error_code(),
                    'error_data' => $result->get_error_data()
                ));
            }
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        if ($debug_enabled) {
            $this->database->add_log('Grouped subscriptions by product ID', 'info', array(
                'products_found' => count($subscriptions_by_product),
                'subscriptions_without_product_id' => $subscriptions_without_product_id,
                'total_processed' => $total_processed,
                'batches_processed' => $batch_count
            ));
        }
        
        // First, clear existing subscription details for today (to handle re-fetches)
        global $wpdb;
        $details_table = $wpdb->prefix . 'keap_reports_daily_subscription_details';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$details_table} WHERE `year` = %d AND `month` = %d AND `day` = %d",
            $current_year, $current_month, $current_day
        ));
        
        // Save daily snapshot for each product
        $saved_count = 0;
        $error_count = 0;
        $save_errors = array();
        $details_saved = 0;
        
        foreach ($subscriptions_by_product as $product_id => $active_count) {
            $saved = $this->database->save_daily_subscription(
                $product_id,
                $current_year,
                $current_month,
                $current_day,
                $active_count
            );
            
            if ($saved) {
                $saved_count++;
                
                // Save individual subscription records for this product
                if (isset($subscriptions_by_product_details[$product_id])) {
                    foreach ($subscriptions_by_product_details[$product_id] as $detail) {
                        $detail_saved = $this->database->save_subscription_detail(
                            $detail['subscription_id'],
                            $detail['contact_id'],
                            $detail['contact_name'],
                            $detail['contact_email'],
                            $product_id,
                            $current_year,
                            $current_month,
                            $current_day,
                            'active'
                        );
                        if ($detail_saved) {
                            $details_saved++;
                        }
                    }
                }
            } else {
                $error_count++;
                $db_error = $wpdb->last_error ? $wpdb->last_error : 'Unknown database error';
                $save_errors[] = "Product {$product_id}: {$db_error}";
                if ($debug_enabled) {
                    $this->database->add_log("Failed to save daily subscription for product {$product_id}: {$db_error}", 'error');
                }
            }
        }
        
        // Also save a record for products with 0 subscriptions (if they exist in product mapping)
        $all_products = $this->database->get_products();
        foreach ($all_products as $product) {
            if (!isset($subscriptions_by_product[$product['product_id']])) {
                $saved = $this->database->save_daily_subscription(
                    $product['product_id'],
                    $current_year,
                    $current_month,
                    $current_day,
                    0
                );
                if ($saved) {
                    $saved_count++;
                }
            }
        }
        
        $message = sprintf(
            'Daily subscription snapshot saved. %d products processed (%d saved, %d errors). Total active subscriptions: %d (processed in %d batches). Individual records saved: %d',
            count($subscriptions_by_product),
            $saved_count,
            $error_count,
            array_sum($subscriptions_by_product),
            $batch_count,
            $details_saved
        );
        
        if ($error_count > 0 && !empty($save_errors)) {
            $message .= '. Errors: ' . implode('; ', array_slice($save_errors, 0, 5));
            if (count($save_errors) > 5) {
                $message .= ' (and ' . (count($save_errors) - 5) . ' more)';
            }
        }
        
        if ($debug_enabled) {
            $this->database->add_log('Daily subscription fetch completed: ' . $message, 'info');
        }
        
        return array(
            'success' => $error_count === 0 || $saved_count > 0, // Success if we saved at least some, or if no errors
            'message' => $message,
            'products_processed' => count($subscriptions_by_product),
            'saved' => $saved_count,
            'errors' => $error_count,
            'total_active' => array_sum($subscriptions_by_product),
            'batches_processed' => $batch_count,
            'total_subscriptions' => $total_processed,
            'save_errors' => $save_errors
        );
    }
    
    /**
     * Fetch and save a paid starter report (snapshot)
     * 
     * For paid starter reports, we store the total count as a snapshot for the current month.
     * This allows tracking growth over time even though the source is a running total.
     * 
     * @param int $report_id Report ID from database
     * @param array $report Report details
     * @param array $data Raw data from API
     * @return array Array with 'success' and 'message' keys
     */
    private function fetch_paid_starter_report($report_id, $report, $data) {
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        
        // If we have an array of records with order dates, aggregate by month (so "this month" = orders in that month only)
        if (is_array($data) && count($data) > 0) {
            $first = $data[0];
            $order_date_key = null;
            if (isset($first['OrderDate'])) {
                $order_date_key = 'OrderDate';
            } elseif (isset($first['Orderdate'])) {
                $order_date_key = 'Orderdate';
            } elseif (isset($first['orderdate'])) {
                $order_date_key = 'orderdate';
            }
            
            if ($order_date_key !== null) {
                $monthly_counts = array(); // year => month => count
                $monthly_revenue = array(); // year => month => revenue
                $order_total_key = null;
                if (isset($first['OrderTotal'])) {
                    $order_total_key = 'OrderTotal';
                } elseif (isset($first['Ordertotal'])) {
                    $order_total_key = 'Ordertotal';
                } elseif (isset($first['ordertotal'])) {
                    $order_total_key = 'ordertotal';
                }
                $records_skipped = 0;
                
                foreach ($data as $record) {
                    if (!is_array($record) || empty($record[$order_date_key])) {
                        $records_skipped++;
                        continue;
                    }
                    $year = null;
                    $month = null;
                    $this->parse_order_date_for_paid_starter($record[$order_date_key], $year, $month);
                    
                    if (!$year || !$month || $month < 1 || $month > 12) {
                        $records_skipped++;
                        continue;
                    }
                    if (!isset($monthly_counts[$year])) {
                        $monthly_counts[$year] = array();
                    }
                    if (!isset($monthly_revenue[$year])) {
                        $monthly_revenue[$year] = array();
                    }
                    if (!isset($monthly_counts[$year][$month])) {
                        $monthly_counts[$year][$month] = 0;
                        $monthly_revenue[$year][$month] = 0.0;
                    }
                    $monthly_counts[$year][$month]++;
                    if ($order_total_key !== null && isset($record[$order_total_key])) {
                        $amt = $record[$order_total_key];
                        if (is_string($amt)) {
                            $amt = preg_replace('/[^0-9.]/', '', $amt);
                        }
                        $monthly_revenue[$year][$month] += floatval($amt);
                    }
                }
                
                $this->database->add_log('Paid starter aggregated by order date', 'info', array(
                    'report_id' => $report['report_id'],
                    'records_total' => count($data),
                    'records_skipped' => $records_skipped,
                    'monthly_counts' => $monthly_counts
                ));
                
                $saved_months = 0;
                foreach ($monthly_counts as $year => $months) {
                    foreach ($months as $month => $count) {
                        $revenue = isset($monthly_revenue[$year][$month]) ? floatval($monthly_revenue[$year][$month]) : 0.0;
                        if ($this->database->save_starter_signups('paid_starter', $year, $month, $count, $revenue)) {
                            $saved_months++;
                        }
                    }
                }
                
                if ($saved_months > 0) {
                    $current_count = isset($monthly_counts[$current_year][$current_month]) ? $monthly_counts[$current_year][$current_month] : 0;
                    return array(
                        'success' => true,
                        'message' => sprintf('Paid starter saved by month: %d months updated. This month (%s %d): %d signups.', $saved_months, date('F', mktime(0, 0, 0, $current_month, 1)), $current_year, $current_count)
                    );
                }
            }
        }
        
        // Fallback: no order dates or non-array data — treat as single snapshot for current month only
        $total_count = 0;
        if (is_numeric($data)) {
            $total_count = intval($data);
        } elseif (is_array($data) && count($data) > 0) {
            if (isset($data[0]['Count']) || isset($data[0]['count']) || isset($data[0]['Total']) || isset($data[0]['total'])) {
                foreach ($data as $item) {
                    if (isset($item['Count'])) {
                        $total_count += intval($item['Count']);
                    } elseif (isset($item['count'])) {
                        $total_count += intval($item['count']);
                    } elseif (isset($item['Total'])) {
                        $total_count += intval($item['Total']);
                    } elseif (isset($item['total'])) {
                        $total_count += intval($item['total']);
                    }
                }
            } else {
                $total_count = count($data);
            }
        }
        
        $saved = $this->database->save_starter_signups('paid_starter', $current_year, $current_month, $total_count, 0.0);
        if ($saved) {
            return array(
                'success' => true,
                'message' => sprintf('Paid starter snapshot saved: %d (current month only; no OrderDate in data)', $total_count)
            );
        }
        return array(
            'success' => false,
            'message' => 'Failed to save paid starter snapshot to database'
        );
    }
    
    /**
     * Parse order date string from Keap (multiple formats) into year and month.
     * Supports: YYYYMMDD, YYYYMMDDTHH:MM:SS, M/D/YYYY, M-D-YYYY, YYYY-MM-DD.
     *
     * @param string $order_date Raw value from API
     * @param int|null $year Output year
     * @param int|null $month Output month (1-12)
     */
    private function parse_order_date_for_paid_starter($order_date, &$year, &$month) {
        $year = null;
        $month = null;
        $order_date = trim((string) $order_date);
        if ($order_date === '') {
            return;
        }
        // Format: 20260106 or 20260106T18:04:22
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $order_date, $m)) {
            $year = intval($m[1]);
            $month = intval($m[2]);
            return;
        }
        // Format: M/D/YYYY or M-D-YYYY (e.g. 2/19/2026, 02/19/2026)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $order_date, $m)) {
            $month = intval($m[1]);
            $day = intval($m[2]);
            $year = intval($m[3]);
            if ($month >= 1 && $month <= 12) {
                return;
            }
            $year = null;
            $month = null;
            return;
        }
        // Format: YYYY-MM-DD or YYYY/MM/DD
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/', $order_date, $m)) {
            $year = intval($m[1]);
            $month = intval($m[2]);
            if ($month >= 1 && $month <= 12) {
                return;
            }
            $year = null;
            $month = null;
        }
    }
    
    /**
     * Fetch and process intensives report data
     * Processes OrderDate and OrderTotal fields, aggregates by month
     * 
     * @param int $report_id Report ID from database
     * @param array $report Report details
     * @param array $data Raw data from API
     * @return array Array with 'success' and 'message' keys
     */
    private function fetch_intensives_report($report_id, $report, $data) {
        if (empty($data) || !is_array($data) || count($data) === 0) {
            $this->database->add_log(
                'Warning: Intensives report "' . $report['name'] . '" (ID: ' . $report['report_id'] . ') returned no results.',
                'warning',
                array(
                    'report_id' => $report['report_id'],
                    'report_name' => $report['name'],
                    'data_type' => gettype($data),
                    'data_count' => is_array($data) ? count($data) : 0
                )
            );
            
            // Save empty data
            $current_year = intval(date('Y'));
            $current_month = intval(date('n'));
            
            $metadata = array(
                'fetched_at' => current_time('mysql'),
                'report_id' => $report['report_id'],
                'report_type' => 'intensives',
                'data_count' => 0,
                'note' => 'No results returned from saved search'
            );
            
            $this->database->save_report_data(
                $report_id,
                $current_year,
                $current_month,
                0,
                $metadata,
                0,
                0.00
            );
            
            return array(
                'success' => true,
                'message' => 'Intensives report fetched but returned no results.',
                'value' => 0,
                'num_orders' => 0,
                'total_amt_sold' => 0.00
            );
        }
        
        // Aggregate data by Year and Month from OrderDate
        // OrderDate format: 20260106T18:04:22 (YYYYMMDDTHH:MM:SS)
        $monthly_data = array(); // year => month => array('num_orders' => X, 'total_amt_sold' => Y)
        
        $records_processed = 0;
        $records_skipped = 0;
        
        foreach ($data as $record_index => $record) {
            if (!is_array($record)) {
                $records_skipped++;
                continue;
            }
            
            // Check if OrderTotal > 0 (only count actual orders)
            $order_total = isset($record['OrderTotal']) ? floatval($record['OrderTotal']) : 0;
            if ($order_total <= 0) {
                $records_skipped++;
                continue;
            }
            
            $records_processed++;
            
            // Parse OrderDate to extract year and month
            $year = null;
            $month = null;
            
            if (isset($record['OrderDate'])) {
                $order_date = $record['OrderDate'];
                // Format: 20260106T18:04:22
                // Extract YYYYMMDD part (first 8 characters)
                if (strlen($order_date) >= 8) {
                    $date_part = substr($order_date, 0, 8);
                    $year = intval(substr($date_part, 0, 4));
                    $month = intval(substr($date_part, 4, 2));
                }
            }
            
            // Skip if we don't have valid year/month
            if (!$year || !$month || $month < 1 || $month > 12) {
                $records_skipped++;
                if ($record_index === 0) {
                    $this->database->add_log("First intensives record skipped - invalid OrderDate", 'warning', array(
                        'year' => $year,
                        'month' => $month,
                        'OrderDate' => isset($record['OrderDate']) ? $record['OrderDate'] : 'not_set',
                        'record' => $record
                    ));
                }
                continue;
            }
            
            // Initialize if not exists
            if (!isset($monthly_data[$year])) {
                $monthly_data[$year] = array();
            }
            if (!isset($monthly_data[$year][$month])) {
                $monthly_data[$year][$month] = array(
                    'num_orders' => 0,
                    'total_amt_sold' => 0.00
                );
            }
            
            // Count order and sum OrderTotal
            $monthly_data[$year][$month]['num_orders'] += 1;
            $monthly_data[$year][$month]['total_amt_sold'] += $order_total;
        }
        
        // Log aggregation results
        $this->database->add_log('Intensives aggregation complete', 'info', array(
            'report_id' => $report['report_id'],
            'records_processed' => $records_processed,
            'records_skipped' => $records_skipped,
            'monthly_data' => $monthly_data
        ));
        
        // Save each month's data
        $saved_count = 0;
        $total_orders = 0;
        $total_amt = 0.00;
        $save_errors = array();
        
        $metadata = array(
            'fetched_at' => current_time('mysql'),
            'report_id' => $report['report_id'],
            'report_type' => 'intensives',
            'data_count' => count($data),
            'records_processed' => $records_processed
        );
        
        foreach ($monthly_data as $year => $months) {
            foreach ($months as $month => $month_data) {
                $month_metadata = $metadata;
                $month_metadata['year'] = $year;
                $month_metadata['month'] = $month;
                
                $saved = $this->database->save_report_data(
                    $report_id,
                    $year,
                    $month,
                    $month_data['total_amt_sold'], // Use total_amt_sold as the value
                    $month_metadata,
                    $month_data['num_orders'],
                    $month_data['total_amt_sold']
                );
                
                if ($saved) {
                    $saved_count++;
                    $total_orders += $month_data['num_orders'];
                    $total_amt += $month_data['total_amt_sold'];
                } else {
                    $save_errors[] = "Failed to save data for {$year}-{$month}";
                }
            }
        }
        
        if ($saved_count > 0) {
            return array(
                'success' => true,
                'message' => sprintf(
                    'Intensives report saved: %d months processed, %d orders, $%s total revenue',
                    $saved_count,
                    $total_orders,
                    number_format($total_amt, 2)
                ),
                'value' => $total_amt,
                'num_orders' => $total_orders,
                'total_amt_sold' => $total_amt
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Failed to save intensives report data. Errors: ' . implode(', ', $save_errors)
            );
        }
    }
}

