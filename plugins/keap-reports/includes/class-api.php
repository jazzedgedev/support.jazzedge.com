<?php
/**
 * Keap API communication class
 * 
 * @package Keap_Reports
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Keap_Reports_API {
    
    /**
     * API base URL
     */
    private $api_base_url = 'https://api.infusionsoft.com/crm/rest/v1';
    
    /**
     * API v2 base URL (for newer endpoints)
     */
    private $api_v2_base_url = 'https://api.infusionsoft.com/crm/rest/v2';
    
    /**
     * XML-RPC endpoint
     */
    private $xmlrpc_endpoint = 'https://api.infusionsoft.com/crm/xmlrpc';
    
    /**
     * Database instance for logging
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Database will be set via set_database() method
    }
    
    /**
     * Set database instance for logging
     * 
     * @param Keap_Reports_Database $database
     */
    public function set_database($database) {
        $this->database = $database;
    }
    
    /**
     * Get API key from settings
     * 
     * @return string
     */
    private function get_api_key() {
        return get_option('keap_reports_api_key', '');
    }
    
    /**
     * Test API connection
     * 
     * @return array Array with 'success' and 'message' keys
     */
    public function test_connection() {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'API key is not configured'
            );
        }
        
        // Try a simple API call to verify credentials
        // Using the contacts endpoint with a limit to test authentication
        $url = $this->api_base_url . '/contacts?limit=1';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection error: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code == 200) {
            return array(
                'success' => true,
                'message' => 'Connection successful'
            );
        } elseif ($response_code == 401) {
            return array(
                'success' => false,
                'message' => 'Invalid API key. Please check your credentials.'
            );
        } else {
            $body = wp_remote_retrieve_body($response);
            return array(
                'success' => false,
                'message' => 'API returned error code: ' . $response_code . '. ' . $body
            );
        }
    }
    
    /**
     * Log debug information
     * 
     * @param string $message
     * @param string $level Log level (info, warning, error, debug)
     * @param array $context Additional context
     */
    private function log_debug($message, $level = 'debug', $context = array()) {
        $debug_enabled = get_option('keap_reports_debug_enabled', false);
        
        // Always log to database if we have database instance
        if ($this->database) {
            $this->database->add_log('[API] ' . $message, $level, $context);
        }
        
        // Also log to error_log if debug is enabled (for backward compatibility)
        if ($debug_enabled) {
            error_log('[Keap Reports API] ' . $message);
        }
    }
    
    /**
     * Fetch report data by querying relevant REST API endpoints based on report type
     * 
     * @param int $report_id Saved search ID
     * @param string $report_uuid Saved search UUID
     * @param string $report_type Type of report (sales, memberships, custom)
     * @return array|WP_Error
     */
    private function fetch_report_data_rest($report_id, $report_uuid, $report_type = 'custom') {
        $this->log_debug('Starting REST API fetch for report_id: ' . $report_id . ', UUID: ' . $report_uuid . ', Type: ' . $report_type);
        
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            $this->log_debug('ERROR: API key is not configured');
            return new WP_Error('no_api_key', 'API key is not configured');
        }
        
        $this->log_debug('API key found (length: ' . strlen($api_key) . ')');
        
        // Get current month date range
        $start_date = date('Y-m-01\T00:00:00\Z'); // First day of current month
        $end_date = date('Y-m-t\T23:59:59\Z'); // Last day of current month
        
        $this->log_debug('Date range: ' . $start_date . ' to ' . $end_date);
        
        // Query based on report type
        switch ($report_type) {
            case 'sales':
                return $this->fetch_sales_data($api_key, $start_date, $end_date);
                
            case 'memberships':
                return $this->fetch_memberships_data($api_key, $start_date, $end_date);
                
            case 'subscriptions':
                return $this->fetch_subscriptions_data($api_key, $start_date, $end_date);
                
            default:
                // For custom, try to get orders/contacts
                return $this->fetch_custom_data($api_key, $start_date, $end_date);
        }
    }
    
    /**
     * Fetch sales data from orders endpoint
     * Based on Keap REST API v1 documentation
     */
    private function fetch_sales_data($api_key, $start_date, $end_date) {
        $this->log_debug('Fetching sales data from orders endpoint');
        $this->log_debug('Date range: ' . $start_date . ' to ' . $end_date);
        
        // Convert dates to simpler format (YYYY-MM-DD) for API
        $start_simple = substr($start_date, 0, 10);
        $end_simple = substr($end_date, 0, 10);
        
        // Query orders for current month
        // Try different parameter formats based on Keap REST API docs
        $url = $this->api_base_url . '/orders';
        
        // Format 1: Standard query with limit (most common)
        $params = array(
            'limit' => 1000
        );
        
        $test_url = $url . '?' . http_build_query($params);
        $this->log_debug('Orders URL: ' . $test_url);
        
        $response = wp_remote_get($test_url, array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            $this->log_debug('Orders request failed: ' . $response->get_error_message());
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $this->log_debug('Orders response code: ' . $response_code);
        $this->log_debug('Orders response body preview: ' . substr($body, 0, 500));
        
        if ($response_code == 200) {
            $data = json_decode($body, true);
            
            $this->log_debug('Orders JSON decoded successfully. Type: ' . gettype($data));
            if (is_array($data)) {
                $this->log_debug('Orders data is array with ' . count($data) . ' items');
                if (!empty($data) && isset($data[0])) {
                    $this->log_debug('First order sample keys: ' . print_r(array_keys($data[0]), true));
                }
            }
            
            if (is_array($data)) {
                // Handle different response structures
                $orders = array();
                
                if (isset($data['orders']) && is_array($data['orders'])) {
                    $orders = $data['orders'];
                } elseif (isset($data[0]) && is_array($data[0])) {
                    // Direct array of orders
                    $orders = $data;
                } else {
                    $this->log_debug('Unexpected orders data structure. Keys: ' . print_r(array_keys($data), true));
                    // Try to use the data as-is
                    $orders = $data;
                }
                
                // Filter orders by date range client-side
                $filtered_orders = array();
                foreach ($orders as $order) {
                    if (!is_array($order)) {
                        continue;
                    }
                    
                    // Try different date field names
                    $order_date = null;
                    if (isset($order['date_created'])) {
                        $order_date = $order['date_created'];
                    } elseif (isset($order['order_date'])) {
                        $order_date = $order['order_date'];
                    } elseif (isset($order['date'])) {
                        $order_date = $order['date'];
                    } elseif (isset($order['created_at'])) {
                        $order_date = $order['created_at'];
                    }
                    
                    if ($order_date) {
                        // Normalize date format for comparison
                        $order_date_simple = substr($order_date, 0, 10);
                        if ($order_date_simple >= $start_simple && $order_date_simple <= $end_simple) {
                            $filtered_orders[] = $order;
                        }
                    } else {
                        // If no date field found, include it (might be in a different format)
                        $this->log_debug('Order missing date field: ' . print_r(array_keys($order), true));
                    }
                }
                
                if (!empty($filtered_orders)) {
                    $this->log_debug('Found ' . count($filtered_orders) . ' orders for current month (filtered from ' . count($orders) . ' total)');
                    return $filtered_orders;
                } elseif (!empty($orders)) {
                    $this->log_debug('Found ' . count($orders) . ' orders but none match date range. Using all orders.');
                    return $orders;
                }
            }
        } else {
            $this->log_debug('Orders request failed with code ' . $response_code . ': ' . substr($body, 0, 500));
        }
        
        return new WP_Error('orders_fetch_failed', 'Failed to fetch orders data. Response: ' . substr($body, 0, 200), array('code' => $response_code));
    }
    
    /**
     * Fetch memberships data from contacts endpoint
     */
    private function fetch_memberships_data($api_key, $start_date, $end_date) {
        $this->log_debug('Fetching memberships data from contacts endpoint');
        
        // Query contacts - we'll need to filter by tags or custom fields
        // For now, get all contacts and filter client-side
        $url = $this->api_base_url . '/contacts';
        $params = array(
            'limit' => 1000
        );
        
        $url .= '?' . http_build_query($params);
        $this->log_debug('Contacts URL: ' . $url);
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            $this->log_debug('Contacts request failed: ' . $response->get_error_message());
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $this->log_debug('Contacts response code: ' . $response_code);
        
        if ($response_code == 200) {
            $data = json_decode($body, true);
            if (is_array($data) && isset($data['contacts'])) {
                $this->log_debug('Found ' . count($data['contacts']) . ' contacts');
                return $data['contacts'];
            } elseif (is_array($data)) {
                return $data;
            }
        }
        
        return new WP_Error('contacts_fetch_failed', 'Failed to fetch contacts data', array('code' => $response_code));
    }
    
    /**
     * Fetch subscriptions/recurring orders data
     * Based on Keap REST API - subscriptions are typically in the subscriptions or recurring orders endpoint
     */
    private function fetch_subscriptions_data($api_key, $start_date, $end_date) {
        $this->log_debug('Fetching subscriptions data');
        $this->log_debug('Date range: ' . $start_date . ' to ' . $end_date);
        
        // Try multiple endpoints - Keap may have subscriptions in different places
        // Option 1: Try /subscriptions endpoint (if available)
        $urls_to_try = array(
            $this->api_base_url . '/subscriptions',
            $this->api_base_url . '/recurringOrders',
            $this->api_v2_base_url . '/subscriptions',
            $this->api_v2_base_url . '/recurringOrders',
            // Also try orders with a filter for recurring
            $this->api_base_url . '/orders'
        );
        
        foreach ($urls_to_try as $url) {
            $this->log_debug('Trying subscriptions endpoint: ' . $url);
            
            $params = array('limit' => 1000);
            $test_url = $url . '?' . http_build_query($params);
            
            $response = wp_remote_get($test_url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                $this->log_debug('Request failed for ' . $url . ': ' . $response->get_error_message());
                continue;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            $this->log_debug('Response code for ' . $url . ': ' . $response_code);
            
            if ($response_code == 200) {
                $data = json_decode($body, true);
                
                if (is_array($data)) {
                    $subscriptions = array();
                    
                    // Handle different response structures
                    if (isset($data['subscriptions']) && is_array($data['subscriptions'])) {
                        $subscriptions = $data['subscriptions'];
                    } elseif (isset($data['recurring_orders']) && is_array($data['recurring_orders'])) {
                        $subscriptions = $data['recurring_orders'];
                    } elseif (isset($data[0]) && is_array($data[0])) {
                        $subscriptions = $data;
                    } else {
                        $this->log_debug('Unexpected subscriptions data structure. Keys: ' . print_r(array_keys($data), true));
                        $this->log_debug('Full response sample: ' . substr($body, 0, 1000));
                        $subscriptions = $data;
                    }
                    
                    $this->log_debug('Found ' . count($subscriptions) . ' subscriptions/recurring orders from ' . $url);
                    
                    // Filter by active status if possible
                    $active_subscriptions = array();
                    foreach ($subscriptions as $sub) {
                        if (!is_array($sub)) {
                            continue;
                        }
                        
                        // Check if subscription is active
                        // Different field names might be used: status, active, subscription_status, etc.
                        $is_active = true; // Default to true if we can't determine
                        
                        if (isset($sub['status'])) {
                            $status = strtolower($sub['status']);
                            $is_active = in_array($status, array('active', '1', 'true', 'enabled'));
                        } elseif (isset($sub['active'])) {
                            $is_active = (bool)$sub['active'];
                        } elseif (isset($sub['subscription_status'])) {
                            $status = strtolower($sub['subscription_status']);
                            $is_active = in_array($status, array('active', '1', 'true', 'enabled'));
                        }
                        
                        if ($is_active) {
                            $active_subscriptions[] = $sub;
                        }
                    }
                    
                    if (!empty($active_subscriptions)) {
                        $this->log_debug('Filtered to ' . count($active_subscriptions) . ' active subscriptions');
                        return $active_subscriptions;
                    } elseif (!empty($subscriptions)) {
                        $this->log_debug('No active filter applied, returning all ' . count($subscriptions) . ' subscriptions');
                        return $subscriptions;
                    }
                } else {
                    $this->log_debug('Response is not an array. Type: ' . gettype($data));
                }
            } elseif ($response_code == 404) {
                $this->log_debug('Endpoint not found (404): ' . $url);
                continue; // Try next endpoint
            } else {
                $this->log_debug('Request failed with code ' . $response_code . ' for ' . $url . ': ' . substr($body, 0, 500));
            }
        }
        
        // If all endpoints failed, return error with details
        return new WP_Error('subscriptions_fetch_failed', 'Failed to fetch subscriptions from any endpoint. Check debug log for details.');
    }
    
    /**
     * Fetch custom data (try orders first)
     */
    private function fetch_custom_data($api_key, $start_date, $end_date) {
        $this->log_debug('Fetching custom data');
        // Try orders endpoint for custom reports
        return $this->fetch_sales_data($api_key, $start_date, $end_date);
    }
    
    /**
     * Fetch saved search results using XML-RPC
     * 
     * @param int $report_id Saved search ID
     * @param string $report_uuid Saved search UUID
     * @return array|WP_Error
     */
    private function fetch_report_data_xmlrpc($report_id, $report_uuid) {
        $this->log_debug('Starting XML-RPC fetch for report_id: ' . $report_id . ', UUID: ' . $report_uuid);
        
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            $this->log_debug('ERROR: API key is not configured');
            return new WP_Error('no_api_key', 'API key is not configured');
        }
        
        // Check if XML-RPC functions are available
        if (!function_exists('xmlrpc_encode_request')) {
            $this->log_debug('ERROR: XML-RPC functions are not available');
            return new WP_Error('xmlrpc_not_available', 'XML-RPC functions are not available. Please enable the xmlrpc extension in PHP.');
        }
        
        $this->log_debug('XML-RPC functions are available');
        
        // XML-RPC request to get saved search results
        // Using SearchService.getSavedSearchResultsAllFields
        $this->log_debug('Encoding XML-RPC request for SearchService.getSavedSearchResultsAllFields');
        $xmlrpc_request = xmlrpc_encode_request('SearchService.getSavedSearchResultsAllFields', array(
            $api_key,
            absint($report_id),
            array() // returnFields - empty means all fields
        ));
        
        $this->log_debug('Sending XML-RPC request to: ' . $this->xmlrpc_endpoint);
        $response = wp_remote_post($this->xmlrpc_endpoint, array(
            'headers' => array(
                'Content-Type' => 'text/xml'
            ),
            'body' => $xmlrpc_request,
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            $this->log_debug('XML-RPC request failed with WP_Error: ' . $response->get_error_message());
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $this->log_debug('XML-RPC response code: ' . $response_code);
        $this->log_debug('XML-RPC response body length: ' . strlen($body));
        
        if ($response_code == 200) {
            $this->log_debug('Decoding XML-RPC response');
            $data = xmlrpc_decode($body);
            
            // Check for XML-RPC fault
            if (function_exists('xmlrpc_is_fault') && xmlrpc_is_fault($data)) {
                $this->log_debug('XML-RPC fault detected: ' . $data['faultString'] . ' (code: ' . $data['faultCode'] . ')');
                return new WP_Error('xmlrpc_fault', $data['faultString'], array('code' => $data['faultCode']));
            }
            
            $this->log_debug('XML-RPC success: Data decoded, type: ' . gettype($data) . ', count: ' . (is_array($data) ? count($data) : 'N/A'));
            return $data;
        } else {
            $this->log_debug('XML-RPC request failed with code: ' . $response_code);
            return new WP_Error('xmlrpc_error', 'XML-RPC request failed with code: ' . $response_code);
        }
    }
    
    /**
     * Fetch report data using REST API endpoints directly
     * 
     * @param int $report_id Saved search ID
     * @param string $report_uuid Saved search UUID
     * @param string $report_type Type of report (sales, memberships, custom)
     * @return array|WP_Error
     */
    public function fetch_report_data($report_id, $report_uuid, $report_type = 'custom') {
        $this->log_debug('=== Starting fetch_report_data ===');
        $this->log_debug('Report ID: ' . $report_id);
        $this->log_debug('Report UUID: ' . $report_uuid);
        $this->log_debug('Report Type: ' . $report_type);
        
        // Use REST API to query endpoints directly based on report type
        $this->log_debug('Attempting REST API fetch based on report type...');
        $result = $this->fetch_report_data_rest($report_id, $report_uuid, $report_type);
        
        if (is_wp_error($result)) {
            $error_code = $result->get_error_code();
            $error_message = $result->get_error_message();
            $this->log_debug('REST API failed: ' . $error_code . ' - ' . $error_message);
        } else {
            $this->log_debug('REST API SUCCESS - Data type: ' . gettype($result) . ', Count: ' . (is_array($result) ? count($result) : 'N/A'));
        }
        
        $this->log_debug('=== fetch_report_data completed ===');
        return $result;
    }
    
    /**
     * Aggregate data from report results
     * 
     * @param array $data Report data from API
     * @param string $report_type Type of report (sales, memberships, custom)
     * @return float Aggregated value
     */
    public function aggregate_data($data, $report_type = 'custom') {
        if (empty($data) || !is_array($data)) {
            $this->log_debug('aggregate_data: Data is empty or not an array');
            return 0;
        }
        
        $this->log_debug('aggregate_data: Processing ' . count($data) . ' records for type: ' . $report_type);
        
        $total = 0;
        
        switch ($report_type) {
            case 'sales':
                // For sales reports from orders endpoint, sum up order totals
                foreach ($data as $order) {
                    if (is_array($order)) {
                        // Keap orders API structure
                        if (isset($order['total'])) {
                            $total += floatval($order['total']);
                        } elseif (isset($order['total_paid'])) {
                            $total += floatval($order['total_paid']);
                        } elseif (isset($order['order_total'])) {
                            $total += floatval($order['order_total']);
                        } elseif (isset($order['subtotal'])) {
                            $total += floatval($order['subtotal']);
                        }
                        // Also check for order items if total isn't at order level
                        if (isset($order['order_items']) && is_array($order['order_items'])) {
                            foreach ($order['order_items'] as $item) {
                                if (isset($item['price'])) {
                                    $total += floatval($item['price']) * (isset($item['quantity']) ? floatval($item['quantity']) : 1);
                                }
                            }
                        }
                    }
                }
                $this->log_debug('aggregate_data: Sales total calculated: ' . $total);
                break;
                
            case 'memberships':
                // For membership reports, count the records
                $total = count($data);
                $this->log_debug('aggregate_data: Memberships count: ' . $total);
                break;
                
            case 'subscriptions':
                // For subscription reports, count active subscriptions
                $total = count($data);
                $this->log_debug('aggregate_data: Subscriptions count: ' . $total);
                // Could also sum subscription values if needed
                // foreach ($data as $sub) {
                //     if (isset($sub['next_bill_amount'])) {
                //         $total += floatval($sub['next_bill_amount']);
                //     }
                // }
                break;
                
            case 'custom':
            default:
                // For custom reports, try to sum numeric fields or count records
                // If it's orders data, try to sum
                if (!empty($data) && isset($data[0]['total'])) {
                    foreach ($data as $record) {
                        if (is_array($record) && isset($record['total'])) {
                            $total += floatval($record['total']);
                        }
                    }
                } else {
                    $total = count($data);
                }
                $this->log_debug('aggregate_data: Custom total: ' . $total);
                break;
        }
        
        return floatval($total);
    }
    
    /**
     * Get metadata from report data
     * 
     * @param array $data Report data from API
     * @return array Metadata array
     */
    public function get_metadata($data) {
        $metadata = array(
            'record_count' => is_array($data) ? count($data) : 0,
            'fetched_at' => current_time('mysql')
        );
        
        return $metadata;
    }
}

