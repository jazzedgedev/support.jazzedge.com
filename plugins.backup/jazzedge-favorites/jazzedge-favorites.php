<?php
/**
 * Plugin Name: Jazzedge Favorites
 * Plugin URI: https://jazzedge.com
 * Description: A plugin to manage lesson favorites that integrates with Jazzedge Practice Hub
 * Version: 1.0.0
 * Author: Jazzedge Team
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JAZZEDGE_FAVORITES_VERSION', '1.0.0');
define('JAZZEDGE_FAVORITES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JAZZEDGE_FAVORITES_PLUGIN_URL', plugin_dir_url(__FILE__));

class Jazzedge_Favorites {
    
    private $db;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Initialize database
        $this->db = new Jazzedge_Favorites_Database();
        
        // Register shortcodes
        add_shortcode('jazzedge_favorites_button', array($this, 'favorites_button_shortcode'));
        
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function activate() {
        $this->db = new Jazzedge_Favorites_Database();
        $this->db->create_tables();
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jazzedge-favorites', JAZZEDGE_FAVORITES_PLUGIN_URL . 'assets/js/favorites.js', array('jquery'), JAZZEDGE_FAVORITES_VERSION, true);
        wp_enqueue_style('jazzedge-favorites', JAZZEDGE_FAVORITES_PLUGIN_URL . 'assets/css/favorites.css', array(), JAZZEDGE_FAVORITES_VERSION);
        
        wp_localize_script('jazzedge-favorites', 'jazzedgeFavorites', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jazzedge_favorites_nonce'),
            'restUrl' => rest_url('jazzedge-favorites/v1/'),
            'restNonce' => wp_create_nonce('wp_rest')
        ));
    }
    
    public function favorites_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => '',
            'url' => '',
            'category' => '',
            'description' => '',
            'button_text' => 'Add to Favorites',
            'class' => 'jf-favorites-btn'
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<p>Please log in to save favorites.</p>';
        }
        
        ob_start();
        ?>
        <div class="jf-favorites-container">
            <button 
                class="<?php echo esc_attr($atts['class']); ?>" 
                data-title="<?php echo esc_attr($atts['title']); ?>"
                data-url="<?php echo esc_attr($atts['url']); ?>"
                data-category="<?php echo esc_attr($atts['category']); ?>"
                data-description="<?php echo esc_attr($atts['description']); ?>">
                ⭐ <?php echo esc_html($atts['button_text']); ?>
            </button>
            <div class="jf-favorites-message" style="display: none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function register_rest_routes() {
        register_rest_route('jazzedge-favorites/v1', '/favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_favorites'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jazzedge-favorites/v1', '/favorites', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_add_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jazzedge-favorites/v1', '/favorites/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('jazzedge-favorites/v1', '/favorites/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_favorite'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
    }
    
    public function check_user_permission($request) {
        return is_user_logged_in();
    }
    
    public function rest_get_favorites($request) {
        $user_id = get_current_user_id();
        $favorites = $this->db->get_favorites($user_id);
        
        return rest_ensure_response(array(
            'success' => true,
            'favorites' => $favorites
        ));
    }
    
    public function rest_add_favorite($request) {
        $user_id = get_current_user_id();
        $data = $request->get_json_params();
        
        $favorite_data = array(
            'user_id' => $user_id,
            'title' => sanitize_text_field($data['title']),
            'url' => esc_url_raw($data['url']),
            'category' => sanitize_text_field($data['category']),
            'description' => sanitize_textarea_field($data['description'])
        );
        
        $result = $this->db->add_favorite($favorite_data);
        
        if ($result) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Favorite added successfully',
                'favorite_id' => $result
            ));
        } else {
            return new WP_Error('add_favorite_failed', 'Failed to add favorite', array('status' => 500));
        }
    }
    
    public function rest_update_favorite($request) {
        $user_id = get_current_user_id();
        $favorite_id = $request['id'];
        $data = $request->get_json_params();
        
        // Check if user owns this favorite
        if (!$this->db->user_owns_favorite($user_id, $favorite_id)) {
            return new WP_Error('permission_denied', 'You do not have permission to update this favorite', array('status' => 403));
        }
        
        $favorite_data = array(
            'title' => sanitize_text_field($data['title']),
            'url' => esc_url_raw($data['url']),
            'category' => sanitize_text_field($data['category']),
            'description' => sanitize_textarea_field($data['description'])
        );
        
        $result = $this->db->update_favorite($favorite_id, $favorite_data);
        
        if ($result) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Favorite updated successfully'
            ));
        } else {
            return new WP_Error('update_favorite_failed', 'Failed to update favorite', array('status' => 500));
        }
    }
    
    public function rest_delete_favorite($request) {
        $user_id = get_current_user_id();
        $favorite_id = $request['id'];
        
        // Check if user owns this favorite
        if (!$this->db->user_owns_favorite($user_id, $favorite_id)) {
            return new WP_Error('permission_denied', 'You do not have permission to delete this favorite', array('status' => 403));
        }
        
        $result = $this->db->delete_favorite($favorite_id);
        
        if ($result) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Favorite deleted successfully'
            ));
        } else {
            return new WP_Error('delete_favorite_failed', 'Failed to delete favorite', array('status' => 500));
        }
    }
}

// Database class
class Jazzedge_Favorites_Database {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'jf_favorites';
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            url text,
            category varchar(100),
            description text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function get_favorites($user_id) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d AND is_active = 1 ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A);
        
        return $results;
    }
    
    public function add_favorite($favorite_data) {
        global $wpdb;
        
        // Check for duplicates
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE user_id = %d AND title = %s AND is_active = 1",
            $favorite_data['user_id'],
            $favorite_data['title']
        ));
        
        if ($existing) {
            return false; // Duplicate found
        }
        
        $result = $wpdb->insert($this->table_name, $favorite_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    public function update_favorite($favorite_id, $favorite_data) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            $favorite_data,
            array('id' => $favorite_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    public function delete_favorite($favorite_id) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array('is_active' => 0),
            array('id' => $favorite_id),
            array('%d'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    public function user_owns_favorite($user_id, $favorite_id) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE id = %d AND user_id = %d AND is_active = 1",
            $favorite_id,
            $user_id
        ));
        
        return !empty($result);
    }
}

// Initialize the plugin
new Jazzedge_Favorites();
