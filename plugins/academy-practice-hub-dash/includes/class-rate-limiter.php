<?php
/**
 * Rate Limiter for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Rate_Limiter {
    
    private static $instance = null;
    private $logger;
    
    // Rate limits (requests per minute)
    private $limits = array(
        'leaderboard' => 30,      // 30 requests per minute
        'user_stats' => 60,       // 60 requests per minute
        'practice_session' => 20, // 20 requests per minute
        'default' => 100           // 100 requests per minute
    );
    
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
        $this->logger = JPH_Logger::get_instance();
    }
    
    /**
     * Check if request is within rate limit
     */
    public function check_rate_limit($endpoint, $user_id = null) {
        $key = $this->get_rate_limit_key($endpoint, $user_id);
        $limit = $this->get_limit_for_endpoint($endpoint);
        
        // Get current count from transient
        $current_count = get_transient($key);
        if ($current_count === false) {
            $current_count = 0;
        }
        
        // Check if limit exceeded
        if ($current_count >= $limit) {
            $this->logger->warning('Rate limit exceeded', array(
                'endpoint' => $endpoint,
                'user_id' => $user_id,
                'current_count' => $current_count,
                'limit' => $limit
            ));
            
            return new WP_Error('rate_limit_exceeded', 'Rate limit exceeded. Please try again later.', array(
                'status' => 429,
                'retry_after' => 60
            ));
        }
        
        // Increment counter
        $current_count++;
        set_transient($key, $current_count, 60); // 1 minute expiry
        
        return true;
    }
    
    /**
     * Get rate limit key for caching
     */
    private function get_rate_limit_key($endpoint, $user_id) {
        $ip = $this->get_client_ip();
        $identifier = $user_id ? "user_{$user_id}" : "ip_{$ip}";
        return "jph_rate_limit_{$endpoint}_{$identifier}";
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Get rate limit for specific endpoint
     */
    private function get_limit_for_endpoint($endpoint) {
        // Extract endpoint type from full endpoint
        if (strpos($endpoint, 'leaderboard') !== false) {
            return $this->limits['leaderboard'];
        } elseif (strpos($endpoint, 'user-stats') !== false) {
            return $this->limits['user_stats'];
        } elseif (strpos($endpoint, 'practice-session') !== false) {
            return $this->limits['practice_session'];
        }
        
        return $this->limits['default'];
    }
    
    /**
     * Add rate limit headers to response
     */
    public function add_rate_limit_headers($response, $endpoint, $user_id = null) {
        $key = $this->get_rate_limit_key($endpoint, $user_id);
        $limit = $this->get_limit_for_endpoint($endpoint);
        $current_count = get_transient($key) ?: 0;
        
        $response->header('X-RateLimit-Limit', $limit);
        $response->header('X-RateLimit-Remaining', max(0, $limit - $current_count));
        $response->header('X-RateLimit-Reset', time() + 60);
        
        return $response;
    }
    
    /**
     * Clear rate limit for user (admin function)
     */
    public function clear_rate_limit($endpoint, $user_id = null) {
        $key = $this->get_rate_limit_key($endpoint, $user_id);
        delete_transient($key);
        
        $this->logger->info('Rate limit cleared', array(
            'endpoint' => $endpoint,
            'user_id' => $user_id
        ));
        
        return true;
    }
}
