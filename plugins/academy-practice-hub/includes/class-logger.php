<?php
/**
 * Logger class for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Logger {
    
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';
    
    private static $instance = null;
    private $log_file = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Set up log file path
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/jph-logs/' . date('Y-m-d') . '.log';
        
        // Create log directory if it doesn't exist
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }
    
    /**
     * Log a message
     */
    public function log($level, $message, $context = array()) {
        // Only log if WordPress debug is enabled or it's an error
        if (!WP_DEBUG && $level !== self::LEVEL_ERROR) {
            return;
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' ' . json_encode($context) : '';
        $log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        // Write to file
        error_log($log_entry, 3, $this->log_file);
        
        // Also log to WordPress debug log if enabled
        if (WP_DEBUG_LOG) {
            error_log("JPH [{$level}]: {$message}" . $context_str);
        }
    }
    
    /**
     * Log error message
     */
    public function error($message, $context = array()) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning($message, $context = array()) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log info message
     */
    public function info($message, $context = array()) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log debug message
     */
    public function debug($message, $context = array()) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Get recent log entries
     */
    public function get_recent_logs($lines = 50) {
        if (!file_exists($this->log_file)) {
            return array();
        }
        
        $logs = file($this->log_file, FILE_IGNORE_NEW_LINES);
        return array_slice($logs, -$lines);
    }
    
    /**
     * Clear logs older than specified days
     */
    public function cleanup_logs($days = 30) {
        $log_dir = dirname($this->log_file);
        $files = glob($log_dir . '/*.log');
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime("-{$days} days")) {
                unlink($file);
            }
        }
    }
}
