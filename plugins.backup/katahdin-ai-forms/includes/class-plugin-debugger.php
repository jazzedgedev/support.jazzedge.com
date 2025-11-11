<?php
/**
 * Katahdin AI Plugin Debugger
 * Comprehensive debugging system for all Katahdin AI plugins
 */

if (!class_exists('Katahdin_AI_Plugin_Debugger')) {
class Katahdin_AI_Plugin_Debugger {
    
    /**
     * Plugin ID for debugging
     */
    private $plugin_id;
    
    /**
     * Plugin name for debugging
     */
    private $plugin_name;
    
    /**
     * Constructor
     */
    public function __construct($plugin_id, $plugin_name) {
        $this->plugin_id = $plugin_id;
        $this->plugin_name = $plugin_name;
    }
    
    /**
     * Run comprehensive debug check
     */
    public function run_comprehensive_debug() {
        $debug_results = array(
            'timestamp' => current_time('Y-m-d H:i:s'),
            'plugin_id' => $this->plugin_id,
            'plugin_name' => $this->plugin_name,
            'wordpress_environment' => $this->check_wordpress_environment(),
            'plugin_loading' => $this->check_plugin_loading(),
            'katahdin_hub_integration' => $this->check_katahdin_hub_integration(),
            'api_key_status' => $this->check_api_key_status(),
            'api_connectivity' => $this->check_api_connectivity(),
            'rest_api_routes' => $this->check_rest_api_routes(),
            'database_tables' => $this->check_database_tables(),
            'file_permissions' => $this->check_file_permissions(),
            'error_logs' => $this->check_error_logs(),
            'plugin_dependencies' => $this->check_plugin_dependencies(),
            'configuration_status' => $this->check_configuration_status()
        );
        
        return $debug_results;
    }
    
    /**
     * Check WordPress environment
     */
    private function check_wordpress_environment() {
        return array(
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'php_memory_limit' => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'wordpress_debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
            'wordpress_debug_log' => defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false,
            'multisite' => is_multisite(),
            'site_url' => get_site_url(),
            'admin_url' => admin_url(),
            'rest_url' => rest_url()
        );
    }
    
    /**
     * Check plugin loading status
     */
    private function check_plugin_loading() {
        $active_plugins = get_option('active_plugins', array());
        $plugin_file = $this->plugin_id . '/' . $this->plugin_id . '.php';
        
        return array(
            'plugin_file_exists' => file_exists(WP_PLUGIN_DIR . '/' . $plugin_file),
            'plugin_active' => in_array($plugin_file, $active_plugins),
            'plugin_loaded' => class_exists($this->get_plugin_class_name()),
            'plugin_function_exists' => function_exists($this->get_plugin_function_name()),
            'plugin_instance_available' => $this->get_plugin_instance_status(),
            'plugin_version' => $this->get_plugin_version(),
            'plugin_constants_defined' => $this->check_plugin_constants()
        );
    }
    
    /**
     * Check Katahdin AI Hub integration
     */
    private function check_katahdin_hub_integration() {
        $hub_status = array(
            'hub_function_exists' => function_exists('katahdin_ai_hub'),
            'hub_class_exists' => class_exists('Katahdin_AI_Hub'),
            'hub_instance_available' => false,
            'hub_api_manager_available' => false,
            'hub_plugin_registry_available' => false,
            'hub_usage_tracker_available' => false,
            'plugin_registered_with_hub' => false,
            'hub_registration_error' => null
        );
        
        if (function_exists('katahdin_ai_hub')) {
            try {
                $hub = katahdin_ai_hub();
                $hub_status['hub_instance_available'] = $hub ? true : false;
                
                if ($hub) {
                    $hub_status['hub_api_manager_available'] = isset($hub->api_manager) && $hub->api_manager;
                    $hub_status['hub_plugin_registry_available'] = isset($hub->plugin_registry) && $hub->plugin_registry;
                    $hub_status['hub_usage_tracker_available'] = isset($hub->usage_tracker) && $hub->usage_tracker;
                    
                    // Check if plugin is registered with hub
                    if ($hub_status['hub_plugin_registry_available']) {
                        try {
                            if (method_exists($hub->plugin_registry, 'get_registered_plugins')) {
                                $registered_plugins = $hub->plugin_registry->get_registered_plugins();
                                $hub_status['plugin_registered_with_hub'] = isset($registered_plugins[$this->plugin_id]);
                            }
                        } catch (Exception $e) {
                            $hub_status['hub_registration_error'] = $e->getMessage();
                        }
                    }
                }
            } catch (Exception $e) {
                $hub_status['hub_registration_error'] = $e->getMessage();
            }
        }
        
        return $hub_status;
    }
    
    /**
     * Check API key status
     */
    private function check_api_key_status() {
        $api_key_option = get_option('katahdin_ai_hub_openai_key');
        
        return array(
            'api_key_option_exists' => !empty($api_key_option),
            'api_key_length' => $api_key_option ? strlen($api_key_option) : 0,
            'api_key_preview' => $api_key_option ? substr($api_key_option, 0, 8) . '...' : 'Not set',
            'api_key_full' => $api_key_option ?: 'Not set', // Show full key for testing
            'api_key_format_valid' => $this->validate_api_key_format($api_key_option),
            'api_key_encrypted' => $this->is_api_key_encrypted($api_key_option)
        );
    }
    
    /**
     * Check API connectivity
     */
    private function check_api_connectivity() {
        $connectivity = array(
            'direct_api_test' => null,
            'hub_api_test' => null,
            'connection_error' => null
        );
        
        if (function_exists('katahdin_ai_hub')) {
            try {
                $hub = katahdin_ai_hub();
                if ($hub && isset($hub->api_manager)) {
                    // Test direct API connection
                    if (method_exists($hub->api_manager, 'test_connection')) {
                        $test_result = $hub->api_manager->test_connection();
                        $connectivity['direct_api_test'] = is_wp_error($test_result) ? 
                            $test_result->get_error_message() : 'Success';
                    }
                    
                    // Test hub API call
                    if (method_exists($hub, 'make_api_call')) {
                        $test_data = array(
                            'messages' => array(
                                array(
                                    'role' => 'user',
                                    'content' => 'Test message for connectivity check'
                                )
                            )
                        );
                        $test_options = array(
                            'model' => 'gpt-3.5-turbo',
                            'max_tokens' => 10
                        );
                        $hub_test = $hub->make_api_call($this->plugin_id, 'chat/completions', $test_data, $test_options);
                        $connectivity['hub_api_test'] = is_wp_error($hub_test) ? 
                            $hub_test->get_error_message() : 'Success';
                    }
                }
            } catch (Exception $e) {
                $connectivity['connection_error'] = $e->getMessage();
            }
        }
        
        return $connectivity;
    }
    
    /**
     * Check REST API routes
     */
    private function check_rest_api_routes() {
        $routes = array();
        
        if (function_exists('rest_get_server')) {
            $all_routes = rest_get_server()->get_routes();
            foreach ($all_routes as $route => $handlers) {
                if (strpos($route, $this->plugin_id) !== false) {
                    $routes[] = array(
                        'route' => $route,
                        'methods' => array_keys($handlers),
                        'accessible' => $this->test_route_accessibility($route)
                    );
                }
            }
        }
        
        return array(
            'plugin_routes_found' => count($routes),
            'routes' => $routes,
            'rest_api_available' => function_exists('rest_get_server')
        );
    }
    
    /**
     * Check database tables
     */
    private function check_database_tables() {
        global $wpdb;
        
        $tables = array();
        $plugin_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}katahdin_%'", ARRAY_N);
        
        foreach ($plugin_tables as $table) {
            $table_name = $table[0];
            $table_info = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$table_name}'");
            
            $tables[] = array(
                'name' => $table_name,
                'rows' => $table_info ? $table_info->Rows : 0,
                'size' => $table_info ? $table_info->Data_length : 0,
                'exists' => true
            );
        }
        
        return array(
            'tables_found' => count($tables),
            'tables' => $tables
        );
    }
    
    /**
     * Check file permissions
     */
    private function check_file_permissions() {
        $plugin_dir = WP_PLUGIN_DIR . '/' . $this->plugin_id;
        
        return array(
            'plugin_directory_exists' => is_dir($plugin_dir),
            'plugin_directory_readable' => is_readable($plugin_dir),
            'plugin_directory_writable' => is_writable($plugin_dir),
            'main_plugin_file_readable' => is_readable($plugin_dir . '/' . $this->plugin_id . '.php'),
            'includes_directory_exists' => is_dir($plugin_dir . '/includes'),
            'assets_directory_exists' => is_dir($plugin_dir . '/assets')
        );
    }
    
    /**
     * Check error logs
     */
    private function check_error_logs() {
        $error_log = ini_get('error_log');
        $recent_errors = array();
        
        if ($error_log && file_exists($error_log)) {
            $log_content = file_get_contents($error_log);
            $log_lines = explode("\n", $log_content);
            $recent_lines = array_slice($log_lines, -50); // Last 50 lines
            
            foreach ($recent_lines as $line) {
                if (strpos($line, 'katahdin') !== false || 
                    strpos($line, $this->plugin_id) !== false ||
                    strpos($line, 'Fatal error') !== false ||
                    strpos($line, 'Parse error') !== false) {
                    $recent_errors[] = htmlspecialchars($line);
                }
            }
        }
        
        return array(
            'error_log_location' => $error_log ?: 'Default',
            'recent_plugin_errors' => $recent_errors,
            'error_count' => count($recent_errors)
        );
    }
    
    /**
     * Check plugin dependencies
     */
    private function check_plugin_dependencies() {
        $dependencies = array();
        
        // Check for Katahdin AI Hub dependency
        $hub_file = 'katahdin-ai-hub/katahdin-ai-hub.php';
        $active_plugins = get_option('active_plugins', array());
        
        $dependencies['katahdin_ai_hub'] = array(
            'required' => true,
            'file_exists' => file_exists(WP_PLUGIN_DIR . '/' . $hub_file),
            'active' => in_array($hub_file, $active_plugins),
            'version' => $this->get_plugin_version_from_header($hub_file)
        );
        
        return $dependencies;
    }
    
    /**
     * Check configuration status
     */
    private function check_configuration_status() {
        $config = array();
        
        // Check plugin-specific options
        $plugin_options = array(
            'katahdin_ai_forms_enabled',
            'katahdin_ai_forms_model',
            'katahdin_ai_forms_max_tokens',
            'katahdin_ai_forms_temperature',
            'katahdin_ai_forms_log_retention_days'
        );
        
        foreach ($plugin_options as $option) {
            if (strpos($option, $this->plugin_id) !== false) {
                $value = get_option($option);
                $config[$option] = array(
                    'exists' => $value !== false,
                    'value' => is_string($value) ? substr($value, 0, 100) : $value,
                    'type' => gettype($value)
                );
            }
        }
        
        return $config;
    }
    
    /**
     * Helper methods
     */
    private function get_plugin_class_name() {
        return 'Katahdin_AI_' . str_replace('-', '_', ucwords($this->plugin_id, '-'));
    }
    
    private function get_plugin_function_name() {
        return str_replace('-', '_', $this->plugin_id);
    }
    
    private function get_plugin_instance_status() {
        $function_name = $this->get_plugin_function_name();
        if (function_exists($function_name)) {
            try {
                $instance = $function_name();
                return $instance ? 'Available' : 'Null';
            } catch (Exception $e) {
                return 'Error: ' . $e->getMessage();
            }
        }
        return 'Function not available';
    }
    
    private function get_plugin_version() {
        $plugin_file = WP_PLUGIN_DIR . '/' . $this->plugin_id . '/' . $this->plugin_id . '.php';
        if (file_exists($plugin_file)) {
            $plugin_data = get_plugin_data($plugin_file);
            return $plugin_data['Version'] ?? 'Unknown';
        }
        return 'Unknown';
    }
    
    private function check_plugin_constants() {
        $constants = array();
        $constant_prefix = strtoupper(str_replace('-', '_', $this->plugin_id));
        
        $possible_constants = array(
            $constant_prefix . '_VERSION',
            $constant_prefix . '_PLUGIN_URL',
            $constant_prefix . '_PLUGIN_PATH',
            $constant_prefix . '_PLUGIN_BASENAME'
        );
        
        foreach ($possible_constants as $constant) {
            $constants[$constant] = defined($constant);
        }
        
        return $constants;
    }
    
    private function validate_api_key_format($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // OpenAI API keys can start with 'sk-' (51 chars) or 'sk-proj-' (longer)
        return preg_match('/^sk-[a-zA-Z0-9]{48}$/', $api_key) || 
               preg_match('/^sk-proj-[a-zA-Z0-9]{20,}$/', $api_key);
    }
    
    private function is_api_key_encrypted($api_key) {
        // Check if the API key appears to be encrypted (base64 encoded, etc.)
        return base64_decode($api_key, true) !== false && 
               base64_decode($api_key) !== $api_key;
    }
    
    private function test_route_accessibility($route) {
        $test_url = rest_url($route);
        $response = wp_remote_get($test_url, array('timeout' => 5));
        
        if (is_wp_error($response)) {
            return array(
                'accessible' => false,
                'error' => $response->get_error_message()
            );
        }
        
        return array(
            'accessible' => true,
            'status_code' => wp_remote_retrieve_response_code($response),
            'response' => wp_remote_retrieve_body($response)
        );
    }
    
    private function get_plugin_version_from_header($plugin_file) {
        $full_path = WP_PLUGIN_DIR . '/' . $plugin_file;
        if (file_exists($full_path)) {
            $plugin_data = get_plugin_data($full_path);
            return $plugin_data['Version'] ?? 'Unknown';
        }
        return 'Not found';
    }
    
    /**
     * Generate debug report HTML
     */
    public function generate_debug_report_html($debug_results) {
        $html = '<div class="katahdin-debug-report">';
        $html .= '<h2>🔍 ' . esc_html($this->plugin_name) . ' Debug Report</h2>';
        $html .= '<p><strong>Generated:</strong> ' . esc_html($debug_results['timestamp']) . '</p>';
        
        foreach ($debug_results as $section => $data) {
            if ($section === 'timestamp' || $section === 'plugin_id' || $section === 'plugin_name') {
                continue;
            }
            
            $html .= '<div class="debug-section">';
            $html .= '<h3>' . esc_html(ucwords(str_replace('_', ' ', $section))) . '</h3>';
            $html .= '<pre>' . esc_html(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
}
}
