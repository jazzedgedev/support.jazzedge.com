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
     * Test API connection for both REST API and XML-RPC
     * 
     * @return array Array with 'rest_api' and 'xmlrpc' keys, each containing 'success' and 'message'
     */
    public function test_connection() {
        $results = array(
            'rest_api' => array('success' => false, 'message' => ''),
            'xmlrpc' => array('success' => false, 'message' => '')
        );
        
        // Test REST API connection
        $api_key = $this->get_api_key();
        if (empty($api_key)) {
            $results['rest_api'] = array(
                'success' => false,
                'message' => 'REST API key is not configured'
            );
        } else {
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
                $results['rest_api'] = array(
                    'success' => false,
                    'message' => 'REST API connection error: ' . $response->get_error_message()
                );
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                
                if ($response_code == 200) {
                    $results['rest_api'] = array(
                        'success' => true,
                        'message' => 'REST API connection successful'
                    );
                } elseif ($response_code == 401) {
                    $results['rest_api'] = array(
                        'success' => false,
                        'message' => 'REST API: Invalid API key. Please check your credentials.'
                    );
                } else {
                    $body = wp_remote_retrieve_body($response);
                    $results['rest_api'] = array(
                        'success' => false,
                        'message' => 'REST API connection failed with code ' . $response_code . ': ' . substr($body, 0, 200)
                    );
                }
            }
        }
        
        // Test XML-RPC connection
        $app_name = get_option('keap_reports_app_name', '');
        $app_key = get_option('keap_reports_app_key', '');
        
        if (empty($app_name) || empty($app_key)) {
            $results['xmlrpc'] = array(
                'success' => false,
                'message' => 'XML-RPC credentials not configured (app name and/or app key missing)'
            );
        } else {
            // Check if iSDK library is available
            $isdk_paths = array(
                '/keap_isdk/infusion_connect.php',
                '/nas/content/live/jazzacademy/keap_isdk/infusion_connect.php',
                ABSPATH . '../keap_isdk/infusion_connect.php',
            );
            
            $isdk_loaded = false;
            foreach ($isdk_paths as $path) {
                if (file_exists($path)) {
                    include_once($path);
                    $isdk_loaded = true;
                    break;
                }
            }
            
            if (!$isdk_loaded) {
                $results['xmlrpc'] = array(
                    'success' => false,
                    'message' => 'XML-RPC: iSDK library not found. Please ensure the iSDK library is installed.'
                );
            } else {
                // Try to get or create iSDK app object
                global $app;
                
                // If $app is not already initialized, try to initialize it
                if (!isset($app) || !is_object($app)) {
                    if (class_exists('iSDK')) {
                        $app = new iSDK;
                        try {
                            $app->cfgCon($app_name, $app_key);
                        } catch (Exception $e) {
                            $results['xmlrpc'] = array(
                                'success' => false,
                                'message' => 'XML-RPC: Failed to connect - ' . $e->getMessage()
                            );
                            return $results;
                        }
                    } else {
                        $results['xmlrpc'] = array(
                            'success' => false,
                            'message' => 'XML-RPC: iSDK class not found'
                        );
                        return $results;
                    }
                }
                
                // Test the connection by trying a simple API call
                try {
                    // Try to get a contact (limit 1) to test the connection
                    // Using a simple query that should work if connected
                    $test_result = $app->dsQuery('Contact', 1, 0, array('Id' => '%'), array('Id'));
                    
                    if ($test_result !== false) {
                        $results['xmlrpc'] = array(
                            'success' => true,
                            'message' => 'XML-RPC connection successful via iSDK'
                        );
                    } else {
                        $results['xmlrpc'] = array(
                            'success' => false,
                            'message' => 'XML-RPC: Connection test query returned false'
                        );
                    }
                } catch (Exception $e) {
                    $results['xmlrpc'] = array(
                        'success' => false,
                        'message' => 'XML-RPC: Connection test failed - ' . $e->getMessage()
                    );
                }
            }
        }
        
        return $results;
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
     * Fetch all pages of data from Keap API with pagination
     * 
     * @param string $url Base URL
     * @param string $api_key API key
     * @param array $params Query parameters
     * @param string $data_key Key in response that contains the data array (e.g., 'contacts', 'orders', 'subscriptions')
     * @return array|WP_Error All collected data
     */
    private function fetch_all_pages($url, $api_key, $params = array(), $data_key = null) {
        $all_data = array();
        $page = 0;
        $limit = isset($params['limit']) ? intval($params['limit']) : 1000;
        $offset = 0;
        $max_pages = 100; // Safety limit
        $total_count = null;
        $has_more = true;
        
        $this->log_debug('Starting paginated fetch from: ' . $url);
        $this->log_debug('Initial params: ' . print_r($params, true));
        
        while ($has_more && $page < $max_pages) {
            // Check if plugin is still active
            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                $this->log_debug('Plugin was deactivated during processing. Stopping pagination at page ' . $page, 'warning');
                break;
            }
            
            // Check if auto-fetch was disabled during processing
            $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
            if (!$auto_fetch_enabled) {
                $this->log_debug('Auto-fetch was disabled during processing. Stopping pagination at page ' . $page, 'warning');
                break;
            }
            
            $page++;
            $this->log_debug("Fetching page {$page} (offset: {$offset})");
            
            // Update offset for this page
            $page_params = $params;
            $page_params['limit'] = $limit;
            $page_params['offset'] = $offset;
            
            $page_url = $url . '?' . http_build_query($page_params);
            $this->log_debug("Page {$page} URL: " . $page_url);
            
            $response = wp_remote_get($page_url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                $this->log_debug('Page ' . $page . ' request failed: ' . $response->get_error_message(), 'error');
                return $response;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $headers = wp_remote_retrieve_headers($response);
            
            $this->log_debug("Page {$page} response code: {$response_code}");
            
            // Log response headers for pagination info
            if (!empty($headers)) {
                $header_array = $headers->getAll();
                $this->log_debug("Page {$page} response headers: " . print_r($header_array, true), 'debug', array('headers' => $header_array));
            }
            
            if ($response_code == 200) {
                $data = json_decode($body, true);
                
                if (!is_array($data)) {
                    $this->log_debug("Page {$page} response is not an array. Type: " . gettype($data), 'warning');
                    $this->log_debug("Page {$page} response body: " . substr($body, 0, 1000), 'debug');
                    break;
                }
                
                // Log full response structure for first page
                if ($page === 1) {
                    $this->log_debug("First page response structure: " . print_r(array_keys($data), true), 'info', array(
                        'response_keys' => array_keys($data),
                        'response_sample' => $data
                    ));
                }
                
                // Extract data based on structure
                $page_data = array();
                
                // Check for pagination metadata
                if (isset($data['count'])) {
                    $this->log_debug("Page {$page} count: " . $data['count']);
                }
                if (isset($data['total'])) {
                    $total_count = intval($data['total']);
                    $this->log_debug("Total records available: {$total_count}");
                }
                if (isset($data['next'])) {
                    $this->log_debug("Next page URL: " . $data['next']);
                }
                
                // Extract the actual data array
                if ($data_key && isset($data[$data_key]) && is_array($data[$data_key])) {
                    $page_data = $data[$data_key];
                } elseif (isset($data[0]) && is_array($data[0])) {
                    // Direct array
                    $page_data = $data;
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    $page_data = $data['items'];
                } elseif (isset($data['results']) && is_array($data['results'])) {
                    $page_data = $data['results'];
                } else {
                    // Try to find any array in the response
                    foreach ($data as $key => $value) {
                        if (is_array($value) && !empty($value) && isset($value[0])) {
                            $page_data = $value;
                            $this->log_debug("Found data array in key: {$key}");
                            break;
                        }
                    }
                }
                
                $page_count = count($page_data);
                $this->log_debug("Page {$page} returned {$page_count} records");
                
                if ($page_count > 0) {
                    $all_data = array_merge($all_data, $page_data);
                    $this->log_debug("Total collected so far: " . count($all_data) . " records");
                    
                    // Check if there's more data
                    if ($page_count < $limit) {
                        // Got fewer records than limit, probably last page
                        $has_more = false;
                        $this->log_debug("Page {$page} returned fewer records than limit ({$page_count} < {$limit}), assuming last page");
                    } elseif ($total_count !== null && count($all_data) >= $total_count) {
                        // Reached total count
                        $has_more = false;
                        $this->log_debug("Reached total count ({$total_count}), stopping pagination");
                    } else {
                        // Move to next page
                        $offset += $limit;
                    }
                } else {
                    // No data on this page, stop
                    $has_more = false;
                    $this->log_debug("Page {$page} returned no data, stopping pagination");
                }
                
                // Log sample of first record from this page
                if (!empty($page_data) && isset($page_data[0])) {
                    $this->log_debug("Page {$page} first record keys: " . print_r(array_keys($page_data[0]), true), 'debug', array(
                        'first_record' => $page_data[0]
                    ));
                }
            } else {
                $this->log_debug("Page {$page} failed with code {$response_code}: " . substr($body, 0, 500), 'error');
                break;
            }
        }
        
        $final_count = count($all_data);
        $this->log_debug("Pagination complete. Total records fetched: {$final_count}", 'info', array(
            'total_fetched' => $final_count,
            'total_available' => $total_count,
            'pages_fetched' => $page
        ));
        
        if ($total_count !== null && $final_count < $total_count) {
            $this->log_debug("WARNING: Fetched {$final_count} records but API reports {$total_count} total available!", 'warning', array(
                'fetched' => $final_count,
                'expected' => $total_count,
                'missing' => $total_count - $final_count
            ));
        }
        
        return $all_data;
    }
    
    /**
     * Fetch report data by querying relevant REST API endpoints based on report type
     * 
     * @param int $report_id Saved search ID
     * @param string $report_uuid Saved search UUID
     * @param string $report_type Type of report (sales, memberships, custom)
     * @param array $filter_product_ids Optional array of product IDs to filter subscriptions by
     * @param bool $is_manual Whether this is a manual fetch (bypasses kill switch/auto-fetch checks)
     * @return array|WP_Error
     */
    private function fetch_report_data_rest($report_id, $report_uuid, $report_type = 'custom', $filter_product_ids = array(), $is_manual = false) {
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
                // Sales reports are saved searches in Keap - fetch directly using report ID/UUID
                return $this->fetch_saved_search_data($api_key, $report_id, $report_uuid, $is_manual);
                
            case 'memberships':
                return $this->fetch_memberships_data($api_key, $start_date, $end_date);
                
            case 'subscriptions':
                if (!empty($filter_product_ids)) {
                    $this->log_debug('Product filter IDs passed: ' . implode(', ', $filter_product_ids));
                }
                return $this->fetch_subscriptions_data($api_key, $start_date, $end_date, $filter_product_ids);
                
            default:
                // Custom reports are also saved searches in Keap - fetch directly using report ID/UUID
                // Do NOT fall back to querying orders, as custom reports should fetch saved search results only
                return $this->fetch_saved_search_data($api_key, $report_id, $report_uuid, $is_manual);
        }
    }
    
    /**
     * Fetch orders in batches with a callback for processing each batch
     * This prevents memory exhaustion when dealing with large numbers of orders
     * 
     * @param string $api_key
     * @param callable $callback Callback function to process each batch (receives array of orders)
     * @param int $batch_size Number of records per batch
     * @return array|WP_Error Array with 'total_processed', 'batches_processed', or WP_Error on failure
     */
    public function fetch_orders_in_batches($api_key, $callback, $batch_size = 100) {
        $this->log_debug('Starting batch fetch of orders');
        
        // Check if plugin is active
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
            $this->log_debug('Plugin is deactivated. Stopping batch fetch.', 'warning');
            return new WP_Error('plugin_deactivated', 'Plugin is deactivated. Please activate the plugin to fetch data.');
        }
        
        // Check if auto-fetch is disabled
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            $this->log_debug('Auto-fetch is disabled. Stopping batch fetch.', 'warning');
            return new WP_Error('auto_fetch_disabled', 'Automatic fetching is disabled. Please enable it in settings to fetch data.');
        }
        
        $url = $this->api_base_url . '/orders';
        
        $total_processed = 0;
        $batches_processed = 0;
        $offset = 0;
        $limit = $batch_size;
        $page = 1;
        $has_more = true;
        $max_pages = 10000; // Safety limit (adjust if needed)
        
        while ($has_more && $page <= $max_pages) {
            // Check kill switch first (most aggressive stop)
            $kill_switch = get_option('keap_reports_kill_switch', 0);
            if ($kill_switch > 0) {
                $this->log_debug('Kill switch activated. Stopping immediately at page ' . $page, 'warning');
                break;
            }
            
            // Check if plugin is still active
            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                $this->log_debug('Plugin was deactivated during processing. Stopping at page ' . $page, 'warning');
                break;
            }
            
            // Check if auto-fetch was disabled during processing
            $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
            if (!$auto_fetch_enabled) {
                $this->log_debug('Auto-fetch was disabled during processing. Stopping at page ' . $page, 'warning');
                break;
            }
            $page_url = $url . '?limit=' . $limit . '&offset=' . $offset;
            $this->log_debug("Fetching page {$page} from {$url} (offset: {$offset})");
            
            $response = wp_remote_get($page_url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 60 // Increased timeout for large responses
            ));
            
            if (is_wp_error($response)) {
                $this->log_debug('Page ' . $page . ' request failed: ' . $response->get_error_message(), 'error');
                break;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($response_code == 200) {
                $data = json_decode($body, true);
                
                if (!is_array($data)) {
                    $this->log_debug("Page {$page} response is not an array", 'warning');
                    break;
                }
                
                // Extract orders from response
                $orders = array();
                if (isset($data['orders']) && is_array($data['orders'])) {
                    $orders = $data['orders'];
                } elseif (isset($data[0]) && is_array($data[0])) {
                    $orders = $data;
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    $orders = $data['items'];
                } elseif (isset($data['results']) && is_array($data['results'])) {
                    $orders = $data['results'];
                }
                
                if (!empty($orders)) {
                    // Check cancellation BEFORE processing batch
                    if (!function_exists('is_plugin_active')) {
                        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                    }
                    if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                        $this->log_debug('Plugin was deactivated during processing. Stopping at page ' . $page, 'warning');
                        break;
                    }
                    $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
                    if (!$auto_fetch_enabled) {
                        $this->log_debug('Auto-fetch was disabled during processing. Stopping at page ' . $page, 'warning');
                        break;
                    }
                    
                    // Process batch via callback
                    $callback_result = $callback($orders);
                    // If callback returns false, stop processing
                    if ($callback_result === false) {
                        $this->log_debug('Callback requested to stop processing at page ' . $page, 'warning');
                        break;
                    }
                    $total_processed += count($orders);
                    $batches_processed++;
                    
                    $this->log_debug("Page {$page}: Processed " . count($orders) . " orders (total so far: {$total_processed})");
                    
                    // Check cancellation AFTER processing batch
                    if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                        $this->log_debug('Plugin was deactivated after processing batch. Stopping.', 'warning');
                        break;
                    }
                    $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
                    if (!$auto_fetch_enabled) {
                        $this->log_debug('Auto-fetch was disabled after processing batch. Stopping.', 'warning');
                        break;
                    }
                    
                    // Check if there's more data
                    if (count($orders) < $limit) {
                        $has_more = false;
                        $this->log_debug("Page {$page} returned fewer records than limit, assuming last page");
                    } else {
                        $offset += $limit;
                        $page++;
                    }
                } else {
                    $has_more = false;
                    $this->log_debug("Page {$page} returned no orders, stopping pagination");
                }
            } else {
                $this->log_debug('API request failed for page ' . $page . ' with code ' . $response_code . ': ' . substr($body, 0, 500), 'error');
                break;
            }
        }
        
        $this->log_debug("Batch fetch complete. Total orders processed: {$total_processed} across {$batches_processed} batches");
        
        return array('success' => true, 'total_processed' => $total_processed, 'batches_processed' => $batches_processed);
    }
    
    /**
     * Fetch sales data from orders endpoint
     * Based on Keap REST API v1 documentation
     * Uses batch processing to prevent memory exhaustion
     */
    private function fetch_sales_data($api_key, $start_date, $end_date) {
        $this->log_debug('Fetching sales data from orders endpoint (batch processing)');
        $this->log_debug('Date range: ' . $start_date . ' to ' . $end_date);
        
        // Convert dates to simpler format (YYYY-MM-DD) for API
        $start_simple = substr($start_date, 0, 10);
        $end_simple = substr($end_date, 0, 10);
        
        // Use batch processing to filter orders as we fetch them
        $filtered_orders = array();
        $total_processed = 0;
        $missing_date_count = 0;
        
        // Define callback to process each batch
        $process_batch_callback = function($orders_batch) use (&$filtered_orders, &$total_processed, &$missing_date_count, $start_simple, $end_simple) {
            // Check kill switch first (most aggressive stop)
            $kill_switch = get_option('keap_reports_kill_switch', 0);
            if ($kill_switch > 0) {
                return false; // Signal to stop immediately
            }
            
            // Check cancellation at start of callback
            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                return false; // Signal to stop
            }
            $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
            if (!$auto_fetch_enabled) {
                return false; // Signal to stop
            }
            
            foreach ($orders_batch as $order) {
                // Check cancellation every 10 orders to stop quickly
                if ($total_processed % 10 == 0) {
                    $kill_switch = get_option('keap_reports_kill_switch', 0);
                    if ($kill_switch > 0) {
                        return false; // Signal to stop immediately
                    }
                    if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                        return false; // Signal to stop
                    }
                    $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
                    if (!$auto_fetch_enabled) {
                        return false; // Signal to stop
                    }
                }
                if (!is_array($order)) {
                    continue;
                }
                
                $total_processed++;
                
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
                    // Track missing date fields (but don't log every one to avoid memory issues)
                    $missing_date_count++;
                }
            }
        };
        
        // Fetch orders in batches
        $result = $this->fetch_orders_in_batches($api_key, $process_batch_callback, 100);
        
        if (is_wp_error($result)) {
            $this->log_debug('Orders batch fetch failed: ' . $result->get_error_message());
            return $result;
        }
        
        if ($missing_date_count > 0) {
            $this->log_debug("Warning: {$missing_date_count} orders were missing date fields and were skipped", 'warning');
        }
        
        if (!empty($filtered_orders)) {
            $this->log_debug('Found ' . count($filtered_orders) . ' orders for date range (filtered from ' . $total_processed . ' total processed)');
            return $filtered_orders;
        } else {
            $this->log_debug('Processed ' . $total_processed . ' orders but none match date range.');
            return array();
        }
    }
    
    /**
     * Fetch memberships data from contacts endpoint
     */
    private function fetch_memberships_data($api_key, $start_date, $end_date) {
        $this->log_debug('Fetching memberships data from contacts endpoint');
        
        // Query contacts - use pagination handler to fetch all pages
        $url = $this->api_base_url . '/contacts';
        $params = array('limit' => 1000);
        
        $contacts = $this->fetch_all_pages($url, $api_key, $params, 'contacts');
        
        if (is_wp_error($contacts)) {
            $this->log_debug('Contacts request failed: ' . $contacts->get_error_message());
            return $contacts;
        }
        
        $this->log_debug('Found ' . count($contacts) . ' contacts');
        return $contacts;
    }
    
    /**
     * Fetch subscriptions in batches with a callback for processing each batch
     * 
     * @param string $api_key
     * @param callable $callback Callback function to process each batch (receives array of subscriptions)
     * @param int $batch_size Number of records per batch
     * @return array|WP_Error Array with 'total_processed', 'batches_processed', or WP_Error on failure
     */
    public function fetch_subscriptions_in_batches($api_key, $callback, $batch_size = 100) {
        $this->log_debug('Starting batch fetch of subscriptions');
        
        // Check if plugin is active
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
            $this->log_debug('Plugin is deactivated. Stopping batch fetch.', 'warning');
            return new WP_Error('plugin_deactivated', 'Plugin is deactivated. Please activate the plugin to fetch data.');
        }
        
        // Check if auto-fetch is disabled
        $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
        if (!$auto_fetch_enabled) {
            $this->log_debug('Auto-fetch is disabled. Stopping batch fetch.', 'warning');
            return new WP_Error('auto_fetch_disabled', 'Automatic fetching is disabled. Please enable it in settings to fetch data.');
        }
        
        // Try multiple endpoints - Keap may have subscriptions in different places
        $urls_to_try = array(
            $this->api_base_url . '/subscriptions',
            $this->api_base_url . '/recurringOrders',
            $this->api_v2_base_url . '/subscriptions',
            $this->api_v2_base_url . '/recurringOrders',
        );
        
        $total_processed = 0;
        $batches_processed = 0;
        $found_endpoint = false;
        
        foreach ($urls_to_try as $url) {
            // Check if auto-fetch was disabled during processing
            $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
            if (!$auto_fetch_enabled) {
                $this->log_debug('Auto-fetch was disabled during processing. Stopping subscriptions fetch.', 'warning');
                break;
            }
            
            $this->log_debug('Trying subscriptions endpoint: ' . $url);
            
            $offset = 0;
            $limit = $batch_size;
            $page = 1;
            $has_more = true;
            
            while ($has_more && $page <= 1000) { // Safety limit
                // Check if plugin is still active
                if (!function_exists('is_plugin_active')) {
                    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                }
                if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                    $this->log_debug('Plugin was deactivated during processing. Stopping at page ' . $page, 'warning');
                    $has_more = false;
                    break;
                }
                
                // Check if auto-fetch was disabled during processing
                $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
                if (!$auto_fetch_enabled) {
                    $this->log_debug('Auto-fetch was disabled during processing. Stopping at page ' . $page, 'warning');
                    $has_more = false;
                    break;
                }
                $page_url = $url . '?limit=' . $limit . '&offset=' . $offset;
                $this->log_debug("Fetching page {$page} from {$url} (offset: {$offset})");
                
                $response = wp_remote_get($page_url, array(
                    'headers' => array(
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $api_key
                    ),
                    'timeout' => 30
                ));
                
                if (is_wp_error($response)) {
                    $this->log_debug('Page ' . $page . ' request failed: ' . $response->get_error_message(), 'error');
                    break;
                }
                
                $response_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                
                if ($response_code == 200) {
                    $data = json_decode($body, true);
                    
                    if (!is_array($data)) {
                        $this->log_debug("Page {$page} response is not an array", 'warning');
                        break;
                    }
                    
                    // Extract subscriptions from response
                    $subscriptions = array();
                    if (isset($data['subscriptions']) && is_array($data['subscriptions'])) {
                        $subscriptions = $data['subscriptions'];
                    } elseif (isset($data[0]) && is_array($data[0])) {
                        $subscriptions = $data;
                    } elseif (isset($data['items']) && is_array($data['items'])) {
                        $subscriptions = $data['items'];
                    }
                    
                    if (!empty($subscriptions)) {
                        // Filter by active status
                        $active_subscriptions = array();
                        foreach ($subscriptions as $sub) {
                            if (!is_array($sub)) {
                                continue;
                            }
                            
                            $is_active = true;
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
                        
                        // Process batch via callback
                        if (!empty($active_subscriptions)) {
                            $callback_result = $callback($active_subscriptions);
                            // If callback returns false, stop processing
                            if ($callback_result === false) {
                                $this->log_debug("Callback requested to stop processing at page {$page}", 'warning');
                                $has_more = false;
                                break;
                            }
                            $total_processed += count($active_subscriptions);
                            $batches_processed++;
                            $found_endpoint = true;
                            
                            $this->log_debug("Page {$page}: Processed " . count($active_subscriptions) . " active subscriptions (total so far: {$total_processed})");
                        }
                        
                        // Check if there's more data
                        if (count($subscriptions) < $limit) {
                            $has_more = false;
                        } else {
                            $offset += $limit;
                            $page++;
                        }
                    } else {
                        $has_more = false;
                    }
                } else {
                    $this->log_debug("Page {$page} failed with code {$response_code}", 'error');
                    break;
                }
            }
            
            // If we found data from this endpoint, stop trying others
            if ($found_endpoint) {
                break;
            }
        }
        
        if (!$found_endpoint) {
            return new WP_Error('subscriptions_fetch_failed', 'Failed to fetch subscriptions from any endpoint.');
        }
        
        $this->log_debug("Batch fetch complete. Total processed: {$total_processed} in {$batches_processed} batches");
        
        return array(
            'total_processed' => $total_processed,
            'batches_processed' => $batches_processed
        );
    }
    
    /**
     * Fetch subscriptions/recurring orders data
     * Based on Keap REST API - subscriptions are typically in the subscriptions or recurring orders endpoint
     * 
     * @param string $api_key
     * @param string $start_date
     * @param string $end_date
     * @param array $filter_product_ids Optional array of product IDs to filter by
     */
    private function fetch_subscriptions_data($api_key, $start_date, $end_date, $filter_product_ids = array()) {
        $this->log_debug('Fetching subscriptions data');
        $this->log_debug('Date range: ' . $start_date . ' to ' . $end_date);
        $this->log_debug('Filter Product IDs: ' . (!empty($filter_product_ids) ? implode(', ', $filter_product_ids) : 'None'));
        
        // Try multiple endpoints - Keap may have subscriptions in different places
        $urls_to_try = array(
            $this->api_base_url . '/subscriptions',
            $this->api_base_url . '/recurringOrders',
            $this->api_v2_base_url . '/subscriptions',
            $this->api_v2_base_url . '/recurringOrders',
            // Also try orders with a filter for recurring
            $this->api_base_url . '/orders'
        );
        
        $all_subscriptions = array();
        
        foreach ($urls_to_try as $url) {
            $this->log_debug('Trying subscriptions endpoint: ' . $url);
            
            // Use pagination handler to fetch all pages
            $params = array('limit' => 1000);
            $subscriptions = $this->fetch_all_pages($url, $api_key, $params, 'subscriptions');
            
            if (is_wp_error($subscriptions)) {
                $this->log_debug('Request failed for ' . $url . ': ' . $subscriptions->get_error_message());
                continue;
            }
            
            if (!empty($subscriptions)) {
                $this->log_debug('Found ' . count($subscriptions) . ' subscriptions/recurring orders from ' . $url);
                
                // Log sample record structure for first endpoint that returns data
                if (empty($all_subscriptions) && !empty($subscriptions) && isset($subscriptions[0])) {
                    $sample_keys = array_keys($subscriptions[0]);
                    $this->log_debug('Sample subscription record keys: ' . implode(', ', $sample_keys), 'info', array(
                        'sample_record' => $subscriptions[0]
                    ));
                }
                
                $all_subscriptions = array_merge($all_subscriptions, $subscriptions);
            } else {
                $this->log_debug('No subscriptions found from ' . $url);
            }
        }
        
        if (empty($all_subscriptions)) {
            return new WP_Error('subscriptions_fetch_failed', 'Failed to fetch subscriptions from any endpoint. Check debug log for details.');
        }
        
        $this->log_debug('Total subscriptions fetched from all endpoints: ' . count($all_subscriptions));
        
        // Filter by active status if possible
        $active_subscriptions = array();
        foreach ($all_subscriptions as $sub) {
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
        
        $this->log_debug('Filtered to ' . count($active_subscriptions) . ' active subscriptions (from ' . count($all_subscriptions) . ' total)');
        
        // Apply product_id filter if specified
        $filtered_by_product = $active_subscriptions;
        if (!empty($filter_product_ids)) {
            $this->log_debug('Applying product ID filter for: ' . implode(', ', $filter_product_ids));
            
            $product_filtered = array();
            $product_id_field_names = array('product_id', 'productId', 'product', 'productid', 'product_id_number');
            
            foreach ($active_subscriptions as $sub) {
                $found_product_id = null;
                
                // Try multiple possible field names for product ID
                foreach ($product_id_field_names as $field_name) {
                    if (isset($sub[$field_name])) {
                        $found_product_id = $sub[$field_name];
                        break;
                    }
                }
                
                // Also check nested structures (e.g., product.id, product.product_id)
                if ($found_product_id === null && isset($sub['product']) && is_array($sub['product'])) {
                    foreach ($product_id_field_names as $field_name) {
                        if (isset($sub['product'][$field_name])) {
                            $found_product_id = $sub['product'][$field_name];
                            break;
                        }
                    }
                }
                
                // Check if this subscription matches any of the filter product IDs
                if ($found_product_id !== null) {
                    // Convert to int for comparison (in case it's a string)
                    $found_product_id_int = intval($found_product_id);
                    if (in_array($found_product_id_int, $filter_product_ids)) {
                        $product_filtered[] = $sub;
                    }
                }
            }
            
            $filtered_by_product = $product_filtered;
            $this->log_debug('Filtered to ' . count($product_filtered) . ' subscriptions matching product IDs: ' . implode(', ', $filter_product_ids), 'info', array(
                'filter_product_ids' => $filter_product_ids,
                'filtered_count' => count($product_filtered),
                'active_count' => count($active_subscriptions),
                'total_count' => count($all_subscriptions)
            ));
            
            // Log a sample of filtered records to verify product IDs
            if (!empty($product_filtered) && isset($product_filtered[0])) {
                $sample_keys = array_keys($product_filtered[0]);
                $sample_product_id = null;
                foreach ($product_id_field_names as $field_name) {
                    if (isset($product_filtered[0][$field_name])) {
                        $sample_product_id = $product_filtered[0][$field_name];
                        break;
                    }
                }
                $this->log_debug('Sample filtered record - Product ID field value: ' . ($sample_product_id !== null ? $sample_product_id : 'NOT FOUND'), 'debug', array(
                    'sample_keys' => $sample_keys,
                    'sample_record' => $product_filtered[0]
                ));
            }
        } else {
            $this->log_debug('No product ID filter specified, returning all active subscriptions');
        }
        
        $this->log_debug('Final count: ' . count($filtered_by_product) . ' subscriptions (active: ' . count($active_subscriptions) . ', total: ' . count($all_subscriptions) . ')');
        return $filtered_by_product;
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
     * Fetch saved search results using XML-RPC via iSDK (preferred method)
     * Falls back to REST API if XML-RPC fails
     * 
     * @param string $api_key
     * @param int $report_id Saved search ID
     * @param string $report_uuid Saved search UUID (not used for XML-RPC)
     * @param bool $is_manual Whether this is a manual fetch (bypasses kill switch/auto-fetch checks)
     * @return array|WP_Error
     */
    private function fetch_saved_search_data($api_key, $report_id, $report_uuid, $is_manual = false) {
        $this->log_debug('Fetching saved search results for report_id: ' . $report_id . ', UUID: ' . $report_uuid);
        
        // Try XML-RPC via iSDK first (works on WP Engine)
        $xmlrpc_result = $this->fetch_saved_search_data_xmlrpc($report_id, $is_manual);
        if (!is_wp_error($xmlrpc_result)) {
            $this->log_debug('Successfully fetched saved search results via XML-RPC/iSDK');
            return $xmlrpc_result;
        }
        
        $this->log_debug('XML-RPC fetch failed, falling back to REST API: ' . $xmlrpc_result->get_error_message());
        
        // Fall back to REST API
        return $this->fetch_saved_search_data_rest($api_key, $report_id, $report_uuid, $is_manual);
    }
    
    /**
     * Fetch saved search results using XML-RPC via iSDK library
     * This method works on WP Engine where native XML-RPC functions may not be available
     * 
     * @param string $report_id Saved search ID
     * @param bool $is_manual Whether this is a manual fetch (bypasses kill switch/auto-fetch checks)
     * @return array|WP_Error
     */
    private function fetch_saved_search_data_xmlrpc($report_id, $is_manual = false) {
        $this->log_debug('Attempting to fetch saved search via XML-RPC/iSDK for report_id: ' . $report_id . ' (is_manual: ' . ($is_manual ? 'true' : 'false') . ')');
        
        // Check if iSDK library is available
        $isdk_paths = array(
            '/keap_isdk/infusion_connect.php',
            '/nas/content/live/jazzacademy/keap_isdk/infusion_connect.php',
            ABSPATH . '../keap_isdk/infusion_connect.php',
        );
        
        $isdk_loaded = false;
        foreach ($isdk_paths as $path) {
            if (file_exists($path)) {
                include_once($path);
                $isdk_loaded = true;
                $this->log_debug('Loaded iSDK from: ' . $path);
                break;
            }
        }
        
        if (!$isdk_loaded) {
            $this->log_debug('iSDK library not found. Tried paths: ' . implode(', ', $isdk_paths), 'warning');
            return new WP_Error('isdk_not_found', 'iSDK library not found. Please ensure the iSDK library is installed.');
        }
        
        // Get iSDK app object from global scope (as used in existing code)
        global $app;
        
        // If $app is not already initialized, try to initialize it
        if (!isset($app) || !is_object($app)) {
            $this->log_debug('iSDK $app object not found in global scope. Attempting to initialize...', 'warning');
            
            // Try to create new iSDK instance
            if (class_exists('iSDK')) {
                $app = new iSDK;
                // Try to get credentials - we'll need app name and API key
                // For now, we'll try common values or get from options
                $app_name = get_option('keap_reports_app_name', 'ft217');
                $app_key = get_option('keap_reports_app_key', '');
                
                if (empty($app_key)) {
                    return new WP_Error('isdk_no_credentials', 'iSDK API key not configured. Please configure app name and API key in settings.');
                }
                
                try {
                    $app->cfgCon($app_name, $app_key);
                    $this->log_debug('Initialized iSDK connection with app: ' . $app_name);
                } catch (Exception $e) {
                    return new WP_Error('isdk_connection_failed', 'Failed to connect to Keap via iSDK: ' . $e->getMessage());
                }
            } else {
                return new WP_Error('isdk_class_not_found', 'iSDK class not found. Please ensure the iSDK library is properly installed.');
            }
        }
        
        try {
            // Fetch saved search results
            // $app->savedSearchAllFields($report_id, $page, $page_size)
            // We'll fetch all pages
            $all_results = array();
            $page = 1;
            $page_size = 1000; // Large page size to minimize API calls
            $max_pages = 100; // Safety limit
            
            while ($page <= $max_pages) {
                // Only check kill switch and auto-fetch for scheduled fetches (not manual)
                if (!$is_manual) {
                    // Check kill switch
                    $kill_switch = get_option('keap_reports_kill_switch', 0);
                    if ($kill_switch > 0) {
                        $this->log_debug('Kill switch activated. Stopping XML-RPC fetch at page ' . $page, 'warning');
                        break;
                    }
                    
                    // Check if plugin is still active
                    if (!function_exists('is_plugin_active')) {
                        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                    }
                    if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                        $this->log_debug('Plugin was deactivated during XML-RPC fetch. Stopping at page ' . $page, 'warning');
                        break;
                    }
                    
                    // Check auto-fetch
                    $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
                    if (!$auto_fetch_enabled) {
                        $this->log_debug('Auto-fetch was disabled during XML-RPC fetch. Stopping at page ' . $page, 'warning');
                        break;
                    }
                }
                
                $this->log_debug("Fetching XML-RPC saved search page {$page} (report_id: {$report_id})");
                
                $page_results = $app->savedSearchAllFields($report_id, $page, $page_size);
                
                if (empty($page_results) || !is_array($page_results)) {
                    // No more results
                    $this->log_debug("Page {$page} returned no results, stopping pagination");
                    break;
                }
                
                $all_results = array_merge($all_results, $page_results);
                $this->log_debug("Page {$page}: Fetched " . count($page_results) . " results (total so far: " . count($all_results) . ")");
                
                // If we got fewer results than page size, we're done
                if (count($page_results) < $page_size) {
                    $this->log_debug("Page {$page} returned fewer results than page size, assuming last page");
                    break;
                }
                
                $page++;
            }
            
            if (!empty($all_results)) {
                $this->log_debug('Successfully fetched ' . count($all_results) . ' results via XML-RPC/iSDK');
                return $all_results;
            } else {
                return new WP_Error('xmlrpc_no_results', 'XML-RPC fetch returned no results');
            }
            
        } catch (Exception $e) {
            $this->log_debug('XML-RPC fetch exception: ' . $e->getMessage(), 'error');
            return new WP_Error('xmlrpc_exception', 'XML-RPC fetch failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Fetch saved search results using REST API (fallback method)
     * Attempts multiple possible endpoints
     * 
     * @param string $api_key
     * @param int $report_id Saved search ID
     * @param string $report_uuid Saved search UUID
     * @param bool $is_manual Whether this is a manual fetch (bypasses kill switch/auto-fetch checks)
     * @return array|WP_Error
     */
    private function fetch_saved_search_data_rest($api_key, $report_id, $report_uuid, $is_manual = false) {
        $this->log_debug('Fetching saved search results via REST API for report_id: ' . $report_id . ', UUID: ' . $report_uuid);
        
        // Try multiple possible REST API endpoints for saved searches
        // NOTE: Keap REST API does NOT support saved searches directly (even v2)
        // They are only available via XML-RPC
        // We'll try all possible endpoints, but they will likely all fail with 404
        $endpoints_to_try = array(
            // Try to get saved search definition first (might work)
            $this->api_v2_base_url . '/savedSearches/' . $report_id,
            $this->api_base_url . '/savedSearches/' . $report_id,
            $this->api_v2_base_url . '/filters/' . $report_id,
            $this->api_base_url . '/filters/' . $report_id,
            // Try to get results directly (will likely fail)
            $this->api_v2_base_url . '/savedSearches/' . $report_id . '/results',
            $this->api_v2_base_url . '/searches/' . $report_id . '/results',
            $this->api_base_url . '/searches/' . $report_id . '/results',
            $this->api_base_url . '/savedSearches/' . $report_id . '/results',
            $this->api_base_url . '/reports/' . $report_id . '/results',
            $this->api_base_url . '/savedFilters/' . $report_id . '/results',
            $this->api_base_url . '/filters/' . $report_id . '/results',
            // Try POST endpoints (execute search)
            $this->api_v2_base_url . '/savedSearches/' . $report_id . '/execute',
            $this->api_v2_base_url . '/searches/' . $report_id . '/execute',
            $this->api_base_url . '/searches/' . $report_id . '/execute',
            $this->api_base_url . '/savedSearches/' . $report_id . '/execute',
        );
        
        // Also try with UUID if provided
        if (!empty($report_uuid)) {
            array_unshift($endpoints_to_try, 
                $this->api_v2_base_url . '/savedSearches/' . $report_uuid,
                $this->api_base_url . '/savedSearches/' . $report_uuid,
                $this->api_v2_base_url . '/filters/' . $report_uuid,
                $this->api_base_url . '/filters/' . $report_uuid,
                $this->api_v2_base_url . '/savedSearches/' . $report_uuid . '/results',
                $this->api_v2_base_url . '/searches/' . $report_uuid . '/results'
            );
            $endpoints_to_try[] = $this->api_base_url . '/searches/' . $report_uuid . '/results';
            $endpoints_to_try[] = $this->api_base_url . '/savedSearches/' . $report_uuid . '/results';
            $endpoints_to_try[] = $this->api_base_url . '/reports/' . $report_uuid . '/results';
            $endpoints_to_try[] = $this->api_base_url . '/savedFilters/' . $report_uuid . '/results';
            $endpoints_to_try[] = $this->api_v2_base_url . '/savedSearches/' . $report_uuid . '/execute';
            $endpoints_to_try[] = $this->api_base_url . '/searches/' . $report_uuid . '/execute';
        }
        
        $all_results = array();
        $working_endpoint = null;
        
        foreach ($endpoints_to_try as $base_url) {
            $this->log_debug('Trying saved search endpoint: ' . $base_url);
            
            // Handle pagination if needed
            $offset = 0;
            $limit = 1000;
            $page = 1;
            $has_more = true;
            $max_pages = 1000; // Safety limit
            
            while ($has_more && $page <= $max_pages) {
                // Only check kill switch and auto-fetch for scheduled fetches (not manual)
                if (!$is_manual) {
                    // Check kill switch first (most aggressive stop)
                    $kill_switch = get_option('keap_reports_kill_switch', 0);
                    if ($kill_switch > 0) {
                        $this->log_debug('Kill switch activated. Stopping saved search fetch at page ' . $page, 'warning');
                        break;
                    }
                    
                    // Check if plugin is still active
                    if (!function_exists('is_plugin_active')) {
                        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                    }
                    if (!is_plugin_active(KEAP_REPORTS_PLUGIN_BASENAME)) {
                        $this->log_debug('Plugin was deactivated during processing. Stopping saved search fetch at page ' . $page, 'warning');
                        break;
                    }
                    
                    // Check if auto-fetch was disabled during processing
                    $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
                    if (!$auto_fetch_enabled) {
                        $this->log_debug('Auto-fetch was disabled during processing. Stopping saved search fetch at page ' . $page, 'warning');
                        break;
                    }
                }
                
                $url = $base_url;
                // Add pagination params if not already in URL
                if (strpos($url, '?') === false) {
                    $url .= '?limit=' . $limit . '&offset=' . $offset;
                } else {
                    $url .= '&limit=' . $limit . '&offset=' . $offset;
                }
                
                if ($page > 1) {
                    $this->log_debug("Fetching page {$page} from saved search (offset: {$offset})");
                }
                
                // Try GET first, but if URL contains /execute, try POST
                $is_post_endpoint = (strpos($url, '/execute') !== false);
                
                if ($is_post_endpoint) {
                    // For execute endpoints, try POST with pagination in body
                    $response = wp_remote_post($url, array(
                        'headers' => array(
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $api_key
                        ),
                        'body' => json_encode(array(
                            'limit' => $limit,
                            'offset' => $offset
                        )),
                        'timeout' => 60
                    ));
                } else {
                    $response = wp_remote_get($url, array(
                        'headers' => array(
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $api_key
                        ),
                        'timeout' => 60
                    ));
                }
                
                if (is_wp_error($response)) {
                    $this->log_debug('Request failed for ' . $url . ': ' . $response->get_error_message());
                    break; // Try next endpoint
                }
                
                $response_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                
                if ($response_code == 200) {
                    $data = json_decode($body, true);
                    
                    if (is_array($data)) {
                        // Check if this is a saved search definition (not results)
                        // If we got the definition, we might be able to extract criteria
                        if (isset($data['id']) && isset($data['name']) && !isset($data['results']) && !isset($data[0])) {
                            $this->log_debug('Received saved search definition instead of results. This endpoint returns the search definition, not results.', 'info', array(
                                'search_definition' => $data
                            ));
                            // This is a definition, not results - try to get results from it
                            // Continue to next endpoint
                            break;
                        }
                        
                        // Extract results from response
                        $page_results = array();
                        if (isset($data['results']) && is_array($data['results'])) {
                            $page_results = $data['results'];
                        } elseif (isset($data['data']) && is_array($data['data'])) {
                            $page_results = $data['data'];
                        } elseif (isset($data[0]) && is_array($data[0])) {
                            $page_results = $data;
                        } elseif (isset($data['items']) && is_array($data['items'])) {
                            $page_results = $data['items'];
                        }
                        
                        if (!empty($page_results)) {
                            $all_results = array_merge($all_results, $page_results);
                            $working_endpoint = $base_url;
                            $this->log_debug("Page {$page}: Fetched " . count($page_results) . " results (total so far: " . count($all_results) . ")");
                            
                            // Check if there's more data
                            if (count($page_results) < $limit) {
                                $has_more = false;
                            } else {
                                $offset += $limit;
                                $page++;
                            }
                        } else {
                            // No results on this page, might be last page or wrong endpoint
                            if ($page == 1) {
                                // First page had no results, try next endpoint
                                break;
                            } else {
                                $has_more = false;
                            }
                        }
                    } else {
                        // Not an array, might be wrong endpoint
                        if ($page == 1) {
                            $this->log_debug('Endpoint returned 200 but response is not an array: ' . $url);
                            break; // Try next endpoint
                        } else {
                            $has_more = false;
                        }
                    }
                } else {
                    // Log full error response for debugging
                    $error_body = substr($body, 0, 1000);
                    $this->log_debug('Endpoint returned code ' . $response_code . ' for ' . $url . ': ' . $error_body, 'error', array(
                        'url' => $url,
                        'response_code' => $response_code,
                        'response_body' => $body
                    ));
                    if ($page == 1) {
                        // First page failed, try next endpoint
                        break;
                    } else {
                        // Later page failed, stop pagination
                        $has_more = false;
                    }
                }
            }
            
            // If we got results from this endpoint, stop trying others
            if (!empty($all_results)) {
                break;
            }
        }
        
        if (!empty($all_results)) {
            $this->log_debug('Successfully fetched ' . count($all_results) . ' results from saved search endpoint: ' . $working_endpoint);
            return $all_results;
        }
        
        // If all endpoints failed, return error with helpful message
        $this->log_debug('Failed to fetch saved search results from any endpoint. All attempted endpoints returned errors. Check logs above for specific error codes and messages.', 'error');
        $this->log_debug('NOTE: Keap REST API may not support saved searches directly. Saved searches are typically only available via XML-RPC API, which may not be available on your server.', 'warning');
        return new WP_Error('saved_search_fetch_failed', 'Could not fetch saved search results from any REST API endpoint. Keap REST API may not support saved searches directly - they are typically only available via XML-RPC. Please check the logs for specific error details.');
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
     * @param array $filter_product_ids Optional array of product IDs to filter subscriptions by
     * @return array|WP_Error
     */
    public function fetch_report_data($report_id, $report_uuid, $report_type = 'custom', $filter_product_ids = array(), $is_manual = false) {
        $this->log_debug('=== Starting fetch_report_data ===');
        
        // Detect if this is a manual fetch (AJAX request or admin POST request)
        // Check multiple conditions to reliably detect manual/admin requests
        $doing_ajax = defined('DOING_AJAX') && DOING_AJAX;
        $is_admin_post = is_admin() && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
        $has_ajax_action = isset($_POST['action']) && strpos($_POST['action'], 'keap_reports') !== false;
        
        $this->log_debug('Manual fetch detection - DOING_AJAX: ' . ($doing_ajax ? 'true' : 'false') . ', is_admin POST: ' . ($is_admin_post ? 'true' : 'false') . ', has ajax action: ' . ($has_ajax_action ? 'true' : 'false') . ', is_manual param: ' . ($is_manual ? 'true' : 'false'));
        
        if (!$is_manual && ($doing_ajax || ($is_admin_post && $has_ajax_action))) {
            $is_manual = true;
            $this->log_debug('Detected manual/admin request - treating as manual fetch (bypassing kill switch/auto-fetch checks)', 'info');
        }
        
        $this->log_debug('Final is_manual value: ' . ($is_manual ? 'true' : 'false'), 'info');
        $this->log_debug('Report ID: ' . $report_id);
        $this->log_debug('Report UUID: ' . $report_uuid);
        $this->log_debug('Report Type: ' . $report_type);
        $this->log_debug('Filter Product IDs: ' . (!empty($filter_product_ids) ? implode(', ', $filter_product_ids) : 'None'));
        
        // Use REST API to query endpoints directly based on report type
        $this->log_debug('Attempting REST API fetch based on report type...');
        $result = $this->fetch_report_data_rest($report_id, $report_uuid, $report_type, $filter_product_ids, $is_manual);
        
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
    
    // ============================================
    // Tag Audit Methods
    // ============================================
    
    /**
     * Get all access tag IDs from Academy Manager settings
     * 
     * @return array Array of tag IDs (flattened from all membership levels)
     */
    public function get_all_access_tag_ids() {
        $keap_tags = get_option('alm_keap_tags', array());
        $all_tag_ids = array();
        
        foreach ($keap_tags as $level => $tags_string) {
            if (!empty($tags_string)) {
                $tag_ids = array_map('trim', explode(',', $tags_string));
                $all_tag_ids = array_merge($all_tag_ids, $tag_ids);
            }
        }
        
        // Remove duplicates and empty values
        $all_tag_ids = array_unique(array_filter($all_tag_ids));
        
        return array_values($all_tag_ids);
    }
    
    /**
     * Get tags for a specific contact
     * 
     * @param int $contact_id Keap contact ID
     * @return array|WP_Error Array of tag IDs or WP_Error
     */
    public function get_contact_tags($contact_id) {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'API key is not configured');
        }
        
        $url = $this->api_base_url . '/contacts/' . absint($contact_id) . '/tags';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($response_code == 200) {
            $data = json_decode($body, true);
            
            // Extract tag IDs from response
            $tag_ids = array();
            if (is_array($data)) {
                foreach ($data as $tag) {
                    if (isset($tag['id'])) {
                        $tag_ids[] = strval($tag['id']);
                    } elseif (isset($tag['tag_id'])) {
                        $tag_ids[] = strval($tag['tag_id']);
                    }
                }
            }
            
            return $tag_ids;
        } else {
            return new WP_Error('api_error', 'Failed to fetch tags. Response code: ' . $response_code);
        }
    }
    
    /**
     * Get subscriptions for a specific contact
     * 
     * @param int $contact_id Keap contact ID
     * @return array|WP_Error Array of subscriptions or WP_Error
     */
    public function get_contact_subscriptions($contact_id) {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'API key is not configured');
        }
        
        // Try multiple endpoints to find subscriptions for this contact
        $urls_to_try = array(
            $this->api_base_url . '/subscriptions?contact_id=' . absint($contact_id),
            $this->api_base_url . '/recurringOrders?contact_id=' . absint($contact_id),
            $this->api_v2_base_url . '/subscriptions?contact_id=' . absint($contact_id),
        );
        
        $all_subscriptions = array();
        
        foreach ($urls_to_try as $url) {
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                continue;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($response_code == 200) {
                $data = json_decode($body, true);
                
                // Extract subscriptions from response
                $subscriptions = array();
                if (isset($data['subscriptions']) && is_array($data['subscriptions'])) {
                    $subscriptions = $data['subscriptions'];
                } elseif (isset($data[0]) && is_array($data[0])) {
                    $subscriptions = $data;
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    $subscriptions = $data['items'];
                }
                
                if (!empty($subscriptions)) {
                    $all_subscriptions = array_merge($all_subscriptions, $subscriptions);
                }
            }
        }
        
        // Also try fetching all subscriptions and filtering by contact_id
        if (empty($all_subscriptions)) {
            // This is a fallback - fetch all and filter (less efficient but more reliable)
            $all_subs = $this->fetch_report_data(0, '', 'subscriptions', array());
            if (!is_wp_error($all_subs) && is_array($all_subs)) {
                foreach ($all_subs as $sub) {
                    if (isset($sub['contact_id']) && intval($sub['contact_id']) == $contact_id) {
                        $all_subscriptions[] = $sub;
                    } elseif (isset($sub['contact']) && isset($sub['contact']['id']) && intval($sub['contact']['id']) == $contact_id) {
                        $all_subscriptions[] = $sub;
                    }
                }
            }
        }
        
        return $all_subscriptions;
    }
    
    /**
     * Check if a subscription is active
     * 
     * @param array $subscription Subscription data from API
     * @return bool True if active, false if expired/inactive
     */
    public function is_subscription_active($subscription) {
        if (!is_array($subscription)) {
            return false;
        }
        
        // Check status field
        if (isset($subscription['status'])) {
            $status = strtolower($subscription['status']);
            return in_array($status, array('active', '1', 'true', 'enabled'));
        }
        
        // Check active field
        if (isset($subscription['active'])) {
            return (bool)$subscription['active'];
        }
        
        // Check subscription_status field
        if (isset($subscription['subscription_status'])) {
            $status = strtolower($subscription['subscription_status']);
            return in_array($status, array('active', '1', 'true', 'enabled'));
        }
        
        // Check end_date - if it exists and is in the future, consider it active
        if (isset($subscription['end_date']) && !empty($subscription['end_date'])) {
            $end_timestamp = strtotime($subscription['end_date']);
            return $end_timestamp > time();
        }
        
        // Default to false if we can't determine
        return false;
    }
    
    /**
     * Get contacts that have specific tags (process in batches)
     * Uses a more efficient approach: fetch subscriptions first, then check contacts
     * 
     * @param array $tag_ids Array of tag IDs to search for
     * @param callable $callback Callback function to process each contact
     * @param int $batch_size Number of contacts to process per batch
     * @return array|WP_Error Array with 'total_processed', 'batches_processed' or WP_Error
     */
    public function get_contacts_with_tags_in_batches($tag_ids, $callback, $batch_size = 100) {
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'API key is not configured');
        }
        
        if (empty($tag_ids)) {
            return new WP_Error('no_tags', 'No tag IDs provided');
        }
        
        $this->log_debug('Starting batch fetch of contacts with tags: ' . implode(', ', $tag_ids));
        
        // More efficient approach: Fetch all contacts in batches and check tags
        // We'll process in smaller batches to avoid memory issues
        $total_processed = 0;
        $batches_processed = 0;
        $offset = 0;
        $limit = 50; // Smaller batches for tag checking (more API calls but less memory)
        $has_more = true;
        $page = 1;
        $contacts_with_tags = array(); // Cache contacts that have tags
        
        while ($has_more && $page <= 2000) { // Safety limit
            // Check if auto-fetch was disabled during processing (for tag audit, this is less critical but still good to check)
            $auto_fetch_enabled = get_option('keap_reports_auto_fetch_enabled', 1);
            // Note: Tag audit is manual, so we don't stop it, but we log if auto-fetch is disabled
            
            $url = $this->api_base_url . '/contacts?limit=' . $limit . '&offset=' . $offset;
            
            if ($page % 10 == 0) {
                $this->log_debug("Fetching contacts page {$page} (offset: {$offset}), found {$total_processed} with access tags so far");
            }
            
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                $this->log_debug('Page ' . $page . ' request failed: ' . $response->get_error_message(), 'error');
                break;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($response_code == 200) {
                $data = json_decode($body, true);
                
                if (!is_array($data)) {
                    $this->log_debug("Page {$page} response is not an array", 'warning');
                    break;
                }
                
                // Extract contacts from response
                $contacts = array();
                if (isset($data['contacts']) && is_array($data['contacts'])) {
                    $contacts = $data['contacts'];
                } elseif (isset($data[0]) && is_array($data[0]) && isset($data[0]['id'])) {
                    $contacts = $data;
                }
                
                if (!empty($contacts)) {
                    // Check each contact's tags (batch the tag requests)
                    $contact_ids = array();
                    foreach ($contacts as $contact) {
                        if (isset($contact['id'])) {
                            $contact_ids[] = intval($contact['id']);
                        }
                    }
                    
                    // Get tags for all contacts in this batch
                    foreach ($contacts as $contact) {
                        if (!isset($contact['id'])) {
                            continue;
                        }
                        
                        $contact_id = intval($contact['id']);
                        $contact_tags = $this->get_contact_tags($contact_id);
                        
                        if (!is_wp_error($contact_tags)) {
                            // Check if contact has any of the access tags
                            $has_access_tag = false;
                            foreach ($tag_ids as $tag_id) {
                                if (in_array(strval($tag_id), $contact_tags)) {
                                    $has_access_tag = true;
                                    break;
                                }
                            }
                            
                            if ($has_access_tag) {
                                // This contact has an access tag, process it
                                $callback($contact, $contact_tags);
                                $total_processed++;
                            }
                        }
                        
                        // Small delay to avoid rate limiting
                        if ($total_processed % 10 == 0) {
                            usleep(100000); // 0.1 second delay every 10 contacts
                        }
                    }
                    
                    $batches_processed++;
                    
                    // Check if there's more data
                    if (count($contacts) < $limit) {
                        $has_more = false;
                    } else {
                        $offset += $limit;
                        $page++;
                    }
                } else {
                    $has_more = false;
                }
            } else {
                $this->log_debug("Page {$page} failed with code {$response_code}", 'error');
                break;
            }
        }
        
        $this->log_debug("Batch fetch complete. Total contacts with access tags: {$total_processed} in {$batches_processed} batches");
        
        return array(
            'total_processed' => $total_processed,
            'batches_processed' => $batches_processed
        );
    }
}

