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
        
        // Fetch data from Keap API (pass report type, filter_product_ids, and is_manual flag)
        $data = $this->api->fetch_report_data($report['report_id'], $report['report_uuid'], $report['report_type'], $filter_product_ids, $is_manual);
        
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
            
            if (is_array($data) && !empty($data)) {
                // Log first 100 records for detailed inspection
                $sample_size = min(100, count($data));
                $sample_records = array_slice($data, 0, $sample_size);
                
                $this->database->add_log('First ' . $sample_size . ' records sample for report "' . $report['name'] . '"', 'debug', array(
                    'report_id' => $report_id,
                    'sample_size' => $sample_size,
                    'total_records' => count($data),
                    'sample_records' => $sample_records
                ));
                
                // Log unique product IDs if subscriptions
                if ($report['report_type'] === 'subscriptions' && !empty($data)) {
                    $product_ids = array();
                    foreach ($data as $record) {
                        if (isset($record['product_id'])) {
                            $product_ids[] = $record['product_id'];
                        }
                    }
                    $unique_product_ids = array_unique($product_ids);
                    $product_id_counts = array_count_values($product_ids);
                    
                    $this->database->add_log('Product ID analysis for report "' . $report['name'] . '"', 'info', array(
                        'report_id' => $report_id,
                        'total_subscriptions' => count($data),
                        'unique_product_ids' => array_values($unique_product_ids),
                        'product_id_counts' => $product_id_counts,
                        'total_unique_products' => count($unique_product_ids)
                    ));
                }
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
        
        // Get current date
        $current_year = intval(date('Y'));
        $current_month = intval(date('n'));
        $current_day = intval(date('j'));
        
        // Track subscriptions by product ID (incrementally)
        $subscriptions_by_product = array();
        $product_id_field_names = array('product_id', 'productId', 'product', 'productid', 'product_id_number');
        $subscriptions_without_product_id = 0;
        $total_processed = 0;
        $batch_count = 0;
        
        // Log sample record on first batch
        $first_batch = true;
        
        // Define callback to process each batch
        $database = $this->database; // Capture database instance for closure
        $process_batch = function($batch) use (&$subscriptions_by_product, &$subscriptions_without_product_id, &$total_processed, &$batch_count, &$first_batch, $product_id_field_names, $debug_enabled, $database) {
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
                    }
                    $subscriptions_by_product[$product_id]++;
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
        
        // Save daily snapshot for each product
        $saved_count = 0;
        $error_count = 0;
        $save_errors = array();
        
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
            } else {
                $error_count++;
                global $wpdb;
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
            'Daily subscription snapshot saved. %d products processed (%d saved, %d errors). Total active subscriptions: %d (processed in %d batches)',
            count($subscriptions_by_product),
            $saved_count,
            $error_count,
            array_sum($subscriptions_by_product),
            $batch_count
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
     * Scan for contacts with expired memberships but active access tags
     * 
     * @return array Array with 'success', 'message', and 'mismatches' keys
     */
    public function scan_tag_mismatches() {
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        if ($debug_enabled) {
            $this->database->add_log('Starting tag mismatch scan', 'info');
        }
        
        // Get all access tag IDs
        $access_tag_ids = $this->api->get_all_access_tag_ids();
        
        if (empty($access_tag_ids)) {
            return array(
                'success' => false,
                'message' => 'No access tags found in Academy Manager settings. Please configure tags first.',
                'mismatches' => array()
            );
        }
        
        if ($debug_enabled) {
            $this->database->add_log('Found ' . count($access_tag_ids) . ' access tag IDs: ' . implode(', ', $access_tag_ids), 'info');
        }
        
        $mismatches = array();
        $contacts_checked = 0;
        
        // Capture class instances for closure
        $api = $this->api;
        $database = $this->database;
        
        // Define callback to process each contact with access tags
        $process_contact = function($contact, $contact_tags) use (&$mismatches, &$contacts_checked, $access_tag_ids, $debug_enabled, $api, $database) {
            $contacts_checked++;
            $contact_id = isset($contact['id']) ? intval($contact['id']) : 0;
            
            if ($contact_id == 0) {
                return;
            }
            
            // Get subscriptions for this contact
            $subscriptions = $api->get_contact_subscriptions($contact_id);
            
            if (is_wp_error($subscriptions)) {
                if ($debug_enabled) {
                    $database->add_log("Failed to get subscriptions for contact {$contact_id}: " . $subscriptions->get_error_message(), 'warning');
                }
                // Still add to list for manual review if we can't determine status
                $mismatches[] = array(
                    'contact_id' => $contact_id,
                    'name' => (isset($contact['given_name']) ? $contact['given_name'] : '') . ' ' . (isset($contact['family_name']) ? $contact['family_name'] : ''),
                    'email' => isset($contact['email_addresses'][0]['email']) ? $contact['email_addresses'][0]['email'] : (isset($contact['email']) ? $contact['email'] : ''),
                    'access_tags' => $contact_tags,
                    'subscription_status' => 'unknown',
                    'has_active' => false,
                    'has_expired' => false,
                    'subscription_count' => 0,
                    'error' => $subscriptions->get_error_message()
                );
                return;
            }
            
            // Check subscription statuses
            $has_active = false;
            $has_expired = false;
            $active_count = 0;
            $expired_count = 0;
            $subscription_details = array();
            
            foreach ($subscriptions as $sub) {
                $is_active = $api->is_subscription_active($sub);
                
                if ($is_active) {
                    $has_active = true;
                    $active_count++;
                } else {
                    $has_expired = true;
                    $expired_count++;
                }
                
                $subscription_details[] = array(
                    'product_id' => isset($sub['product_id']) ? $sub['product_id'] : (isset($sub['productId']) ? $sub['productId'] : 'N/A'),
                    'status' => isset($sub['status']) ? $sub['status'] : (isset($sub['subscription_status']) ? $sub['subscription_status'] : 'unknown'),
                    'is_active' => $is_active,
                    'end_date' => isset($sub['end_date']) ? $sub['end_date'] : null
                );
            }
            
            // Determine if this is a mismatch
            // Mismatch = has access tag but NO active subscriptions (only expired or none)
            $is_mismatch = false;
            $status_text = '';
            
            if (empty($subscriptions)) {
                // No subscriptions at all but has access tag
                $is_mismatch = true;
                $status_text = 'No subscriptions found';
            } elseif ($has_active) {
                // Has active subscription - this is OK (even if they also have expired ones)
                $is_mismatch = false;
                $status_text = 'Has active subscription(s)';
            } elseif ($has_expired) {
                // Only expired subscriptions - this is a mismatch
                $is_mismatch = true;
                $status_text = 'All subscriptions expired';
            }
            
            // Add to list if mismatch OR if they have both active and expired (for manual review)
            if ($is_mismatch || ($has_active && $has_expired)) {
                $mismatches[] = array(
                    'contact_id' => $contact_id,
                    'name' => trim((isset($contact['given_name']) ? $contact['given_name'] : '') . ' ' . (isset($contact['family_name']) ? $contact['family_name'] : '')),
                    'email' => isset($contact['email_addresses'][0]['email']) ? $contact['email_addresses'][0]['email'] : (isset($contact['email']) ? $contact['email'] : ''),
                    'access_tags' => $contact_tags,
                    'subscription_status' => $status_text,
                    'has_active' => $has_active,
                    'has_expired' => $has_expired,
                    'active_count' => $active_count,
                    'expired_count' => $expired_count,
                    'subscription_count' => count($subscriptions),
                    'subscription_details' => $subscription_details
                );
            }
            
            // Log progress every 50 contacts
            if ($debug_enabled && $contacts_checked % 50 == 0) {
                $database->add_log("Checked {$contacts_checked} contacts, found " . count($mismatches) . " mismatches so far", 'info');
            }
        };
        
        // Fetch contacts with tags in batches
        $result = $this->api->get_contacts_with_tags_in_batches($access_tag_ids, $process_contact, 100);
        
        if (is_wp_error($result)) {
            return array(
                'success' => false,
                'message' => 'Error scanning contacts: ' . $result->get_error_message(),
                'mismatches' => array()
            );
        }
        
        $message = sprintf(
            'Scan complete. Checked %d contacts with access tags. Found %d potential mismatches.',
            $contacts_checked,
            count($mismatches)
        );
        
        if ($debug_enabled) {
            $this->database->add_log('Tag mismatch scan completed: ' . $message, 'info');
        }
        
        return array(
            'success' => true,
            'message' => $message,
            'mismatches' => $mismatches,
            'contacts_checked' => $contacts_checked,
            'total_mismatches' => count($mismatches)
        );
    }
}

