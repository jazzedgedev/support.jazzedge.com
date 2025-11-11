<?php
/**
 * Cache Manager for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Cache {
    
    private static $instance = null;
    private $logger;
    private $cache_prefix = 'jph_cache_';
    private $default_expiry = 300; // 5 minutes
    
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
     * Get cached data
     */
    public function get($key, $group = 'default') {
        $cache_key = $this->get_cache_key($key, $group);
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            $this->logger->debug('Cache hit', array('key' => $key, 'group' => $group));
            return $cached_data;
        }
        
        $this->logger->debug('Cache miss', array('key' => $key, 'group' => $group));
        return false;
    }
    
    /**
     * Set cached data
     */
    public function set($key, $data, $group = 'default', $expiry = null) {
        $cache_key = $this->get_cache_key($key, $group);
        $expiry = $expiry ?: $this->default_expiry;
        
        $result = set_transient($cache_key, $data, $expiry);
        
        if ($result) {
            $this->logger->debug('Cache set', array('key' => $key, 'group' => $group, 'expiry' => $expiry));
        } else {
            $this->logger->warning('Cache set failed', array('key' => $key, 'group' => $group));
        }
        
        return $result;
    }
    
    /**
     * Delete cached data
     */
    public function delete($key, $group = 'default') {
        $cache_key = $this->get_cache_key($key, $group);
        $result = delete_transient($cache_key);
        
        if ($result) {
            $this->logger->debug('Cache deleted', array('key' => $key, 'group' => $group));
        }
        
        return $result;
    }
    
    /**
     * Clear all cache for a group
     */
    public function clear_group($group) {
        global $wpdb;
        
        $pattern = $this->cache_prefix . $group . '_%';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        ));
        
        $this->logger->info('Cache group cleared', array('group' => $group));
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear_all() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        ));
        
        $this->logger->info('All cache cleared');
        return true;
    }
    
    /**
     * Get cache key
     */
    private function get_cache_key($key, $group) {
        return $this->cache_prefix . $group . '_' . md5($key);
    }
    
    /**
     * Cache leaderboard data
     */
    public function cache_leaderboard($sort_by, $sort_order, $limit, $offset, $data) {
        $key = "leaderboard_{$sort_by}_{$sort_order}_{$limit}_{$offset}";
        return $this->set($key, $data, 'leaderboard', 300); // 5 minutes
    }
    
    /**
     * Get cached leaderboard data
     */
    public function get_cached_leaderboard($sort_by, $sort_order, $limit, $offset) {
        $key = "leaderboard_{$sort_by}_{$sort_order}_{$limit}_{$offset}";
        return $this->get($key, 'leaderboard');
    }
    
    /**
     * Cache leaderboard stats
     */
    public function cache_leaderboard_stats($data) {
        return $this->set('stats', $data, 'leaderboard', 600); // 10 minutes
    }
    
    /**
     * Get cached leaderboard stats
     */
    public function get_cached_leaderboard_stats() {
        return $this->get('stats', 'leaderboard');
    }
    
    /**
     * Cache user stats
     */
    public function cache_user_stats($user_id, $data) {
        $key = "user_stats_{$user_id}";
        return $this->set($key, $data, 'user_stats', 300); // 5 minutes
    }
    
    /**
     * Get cached user stats
     */
    public function get_cached_user_stats($user_id) {
        $key = "user_stats_{$user_id}";
        return $this->get($key, 'user_stats');
    }
    
    /**
     * Invalidate user-related cache
     */
    public function invalidate_user_cache($user_id) {
        $this->delete("user_stats_{$user_id}", 'user_stats');
        $this->clear_group('leaderboard'); // Leaderboard changes when user stats change
        $this->logger->info('User cache invalidated', array('user_id' => $user_id));
    }
    
    /**
     * Get cache statistics
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        ));
        
        return array(
            'total_cached_items' => $count,
            'cache_prefix' => $this->cache_prefix,
            'default_expiry' => $this->default_expiry
        );
    }
}
