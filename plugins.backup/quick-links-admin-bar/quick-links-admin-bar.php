<?php
/**
 * Plugin Name: Quick Links Admin Bar
 * Plugin URI: https://katahdin.ai/
 * Description: Add customizable quick links to the WordPress admin bar for easy access to frequently used pages and tools.
 * Version: 1.0.0
 * Author: Katahdin AI
 * Author URI: https://katahdin.ai/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-links-admin-bar
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QLAB_VERSION', '1.0.0');
define('QLAB_PLUGIN_FILE', __FILE__);
define('QLAB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QLAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QLAB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class QuickLinksAdminBar {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
        add_action('admin_init', array($this, 'handle_quick_links'));
        
        // AJAX handlers for quick links
        add_action('wp_ajax_qlab_save_quick_link', array($this, 'save_quick_link_ajax'));
        add_action('wp_ajax_qlab_delete_quick_link', array($this, 'delete_quick_link_ajax'));
        add_action('wp_ajax_qlab_update_quick_link', array($this, 'update_quick_link_ajax'));
        add_action('wp_ajax_qlab_reorder_quick_links', array($this, 'reorder_quick_links_ajax'));
        
        // Add admin bar menu for quick links
        add_action('admin_bar_menu', array($this, 'add_quick_links_admin_bar'), 999);
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('quick-links-admin-bar', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Quick Links', 'quick-links-admin-bar'),
            __('Quick Links', 'quick-links-admin-bar'),
            'manage_options',
            'quick-links-admin-bar',
            array($this, 'quick_links_page'),
            'dashicons-admin-links',
            30
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'quick-links-admin-bar') !== false) {
            wp_enqueue_style('qlab-admin', QLAB_PLUGIN_URL . 'assets/css/admin.css', array(), QLAB_VERSION);
        }
    }
    
    /**
     * Enqueue admin bar styles globally
     */
    public function enqueue_admin_bar_styles() {
        if (is_admin_bar_showing()) {
            wp_enqueue_style('qlab-admin-bar', QLAB_PLUGIN_URL . 'assets/css/admin.css', array(), QLAB_VERSION);
        }
    }
    
    /**
     * Handle quick links requests
     */
    public function handle_quick_links() {
        if (isset($_GET['page']) && $_GET['page'] === 'quick-links-admin-bar') {
            // Security check
            if (!current_user_can('manage_options')) {
                wp_die('Access denied. You do not have permission to manage quick links.');
            }
        }
    }
    
    /**
     * Quick Links page callback
     */
    public function quick_links_page() {
        $quick_links = get_option('qlab_quick_links', array());
        
        ?>
        <div class="wrap">
            <h1>🔗 Quick Links</h1>
            <p>Manage quick links that appear in the admin bar for easy access.</p>
            
            <!-- Add New Quick Link Form -->
            <div class="card" style="max-width: 600px; margin: 20px 0;">
                <h2>Add New Quick Link</h2>
                <form id="add-quick-link-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="link_title">Title</label>
                            </th>
                            <td>
                                <input type="text" id="link_title" name="link_title" class="regular-text" required>
                                <p class="description">The display name for this link</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="link_url">URL</label>
                            </th>
                            <td>
                                <input type="url" id="link_url" name="link_url" class="regular-text" required>
                                <p class="description">The URL this link should point to</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="link_new_window">Open in New Window</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="link_new_window" name="link_new_window" value="1">
                                    Open this link in a new window/tab
                                </label>
                                <p class="description">When enabled, the link will open in a new window/tab</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary">Add Quick Link</button>
                    </p>
                </form>
            </div>
            
            <!-- Existing Quick Links -->
            <div class="card" style="max-width: 800px;">
                <h2>Existing Quick Links</h2>
                <?php if (empty($quick_links)): ?>
                    <p>No quick links found. Add your first quick link above.</p>
                <?php else: ?>
                    <div class="quick-links-container">
                        <div id="quick-links-list" class="sortable-links">
                            <?php foreach ($quick_links as $index => $link): ?>
                                <div class="quick-link-item" data-index="<?php echo esc_attr($index); ?>">
                                    <div class="quick-link-handle" title="Drag to reorder">⋮⋮</div>
                                    <div class="quick-link-content">
                                        <div class="quick-link-display">
                                            <div class="quick-link-title">
                                                <?php echo esc_html($link['title']); ?>
                                                <?php if (!empty($link['new_window'])): ?>
                                                    <span class="new-window-indicator" title="Opens in new window">🔗</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="quick-link-url-display">
                                                <a href="<?php echo esc_url($link['url']); ?>" 
                                                   <?php echo (!empty($link['new_window'])) ? 'target="_blank"' : ''; ?> 
                                                   class="quick-link-url">
                                                    <?php echo esc_html($link['url']); ?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="quick-link-edit" style="display: none;">
                                            <input type="text" class="edit-title" value="<?php echo esc_attr($link['title']); ?>" placeholder="Title">
                                            <input type="url" class="edit-url" value="<?php echo esc_attr($link['url']); ?>" placeholder="URL">
                                            <label class="edit-new-window">
                                                <input type="checkbox" class="edit-new-window-checkbox" <?php echo (!empty($link['new_window'])) ? 'checked' : ''; ?>>
                                                Open in new window
                                            </label>
                                        </div>
                                    </div>
                                    <div class="quick-link-actions">
                                        <button type="button" class="button button-small edit-quick-link" data-index="<?php echo esc_attr($index); ?>">
                                            Edit
                                        </button>
                                        <button type="button" class="button button-small save-quick-link" data-index="<?php echo esc_attr($index); ?>" style="display: none;">
                                            Save
                                        </button>
                                        <button type="button" class="button button-small cancel-edit" data-index="<?php echo esc_attr($index); ?>" style="display: none;">
                                            Cancel
                                        </button>
                                        <button type="button" class="button button-link-delete delete-quick-link" 
                                                data-index="<?php echo esc_attr($index); ?>">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .quick-links-container {
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
        }
        
        .sortable-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .quick-link-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .quick-link-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .quick-link-item.ui-sortable-helper {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: rotate(2deg);
        }
        
        .quick-link-handle {
            cursor: move;
            color: #999;
            font-size: 18px;
            line-height: 1;
            padding: 8px;
            user-select: none;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .quick-link-handle:hover {
            color: #666;
            background: #e9ecef;
        }
        
        .quick-link-content {
            flex: 1;
        }
        
        .quick-link-display {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .quick-link-title {
            font-weight: 600;
            font-size: 16px;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .new-window-indicator {
            font-size: 14px;
            opacity: 0.7;
        }
        
        .quick-link-url-display {
            margin: 0;
        }
        
        .quick-link-url {
            color: #0073aa;
            text-decoration: none;
            font-size: 14px;
            word-break: break-all;
        }
        
        .quick-link-url:hover {
            text-decoration: underline;
        }
        
        .quick-link-edit {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .quick-link-edit input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .quick-link-edit input:focus {
            outline: none;
            border-color: #f04e23;
            box-shadow: 0 0 0 2px rgba(240, 78, 35, 0.1);
        }
        
        .edit-new-window {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 14px;
        }
        
        .edit-new-window input[type="checkbox"] {
            margin: 0;
        }
        
        .quick-link-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 15px 0;
            display: none;
            border-left: 4px solid #28a745;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 15px 0;
            display: none;
            border-left: 4px solid #dc3545;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .quick-link-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .quick-link-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .quick-links-container {
                padding: 15px;
            }
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Quick Link Form
            document.getElementById('add-quick-link-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'qlab_save_quick_link');
                formData.append('nonce', '<?php echo wp_create_nonce('qlab_nonce'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showMessage('Error adding quick link: ' + (data.data || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    showMessage('Network error. Please try again.', 'error');
                });
            });
            
            // Edit Quick Link
            document.querySelectorAll('.edit-quick-link').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const item = document.querySelector(`[data-index="${index}"]`);
                    const display = item.querySelector('.quick-link-display');
                    const edit = item.querySelector('.quick-link-edit');
                    const editBtn = item.querySelector('.edit-quick-link');
                    const saveBtn = item.querySelector('.save-quick-link');
                    const cancelBtn = item.querySelector('.cancel-edit');
                    
                    display.style.display = 'none';
                    edit.style.display = 'flex';
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'inline-block';
                    cancelBtn.style.display = 'inline-block';
                });
            });
            
            // Save Quick Link
            document.querySelectorAll('.save-quick-link').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const item = document.querySelector(`[data-index="${index}"]`);
                    const title = item.querySelector('.edit-title').value;
                    const url = item.querySelector('.edit-url').value;
                    const newWindow = item.querySelector('.edit-new-window-checkbox').checked;
                    
                    if (!title || !url) {
                        showMessage('Title and URL are required.', 'error');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('action', 'qlab_update_quick_link');
                    formData.append('index', index);
                    formData.append('title', title);
                    formData.append('url', url);
                    formData.append('new_window', newWindow ? '1' : '0');
                    formData.append('nonce', '<?php echo wp_create_nonce('qlab_nonce'); ?>');
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            showMessage('Error updating quick link: ' + (data.data || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        showMessage('Network error. Please try again.', 'error');
                    });
                });
            });
            
            // Cancel Edit
            document.querySelectorAll('.cancel-edit').forEach(button => {
                button.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');
                    const item = document.querySelector(`[data-index="${index}"]`);
                    const display = item.querySelector('.quick-link-display');
                    const edit = item.querySelector('.quick-link-edit');
                    const editBtn = item.querySelector('.edit-quick-link');
                    const saveBtn = item.querySelector('.save-quick-link');
                    const cancelBtn = item.querySelector('.cancel-edit');
                    
                    display.style.display = 'flex';
                    edit.style.display = 'none';
                    editBtn.style.display = 'inline-block';
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';
                });
            });
            
            // Delete Quick Link
            document.querySelectorAll('.delete-quick-link').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this quick link?')) {
                        const index = this.getAttribute('data-index');
                        const formData = new FormData();
                        formData.append('action', 'qlab_delete_quick_link');
                        formData.append('index', index);
                        formData.append('nonce', '<?php echo wp_create_nonce('qlab_nonce'); ?>');
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                showMessage('Error deleting quick link: ' + (data.data || 'Unknown error'), 'error');
                            }
                        })
                        .catch(error => {
                            showMessage('Network error. Please try again.', 'error');
                        });
                    }
                });
            });
            
            // Initialize sortable
            if (typeof jQuery !== 'undefined' && jQuery.ui && jQuery.ui.sortable) {
                jQuery('#quick-links-list').sortable({
                    handle: '.quick-link-handle',
                    placeholder: 'quick-link-placeholder',
                    update: function(event, ui) {
                        const order = [];
                        jQuery('#quick-links-list .quick-link-item').each(function() {
                            order.push(jQuery(this).attr('data-index'));
                        });
                        
                        const formData = new FormData();
                        formData.append('action', 'qlab_reorder_quick_links');
                        formData.append('order', JSON.stringify(order));
                        formData.append('nonce', '<?php echo wp_create_nonce('qlab_nonce'); ?>');
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage('Order updated successfully!', 'success');
                            } else {
                                showMessage('Error updating order: ' + (data.data || 'Unknown error'), 'error');
                            }
                        })
                        .catch(error => {
                            showMessage('Network error. Please try again.', 'error');
                        });
                    }
                });
            }
            
            function showMessage(message, type) {
                const existing = document.querySelector('.success-message, .error-message');
                if (existing) {
                    existing.remove();
                }
                
                const messageDiv = document.createElement('div');
                messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
                messageDiv.textContent = message;
                messageDiv.style.display = 'block';
                
                document.querySelector('.wrap h1').insertAdjacentElement('afterend', messageDiv);
                
                setTimeout(() => {
                    messageDiv.remove();
                }, 3000);
            }
        });
        </script>
        <?php
    }
    
    /**
     * Add quick links to admin bar
     */
    public function add_quick_links_admin_bar($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $quick_links = get_option('qlab_quick_links', array());
        
        if (empty($quick_links)) {
            return;
        }
        
        // Add main menu item to the right side (secondary menu)
        $wp_admin_bar->add_menu(array(
            'id' => 'qlab-quick-links',
            'title' => 'Quick Links',
            'href' => '#',
            'parent' => 'top-secondary',
            'meta' => array(
                'class' => 'qlab-quick-links-menu',
                'title' => 'Quick Links'
            )
        ));
        
        // Add individual links
        foreach ($quick_links as $index => $link) {
            $title = $link['title'];
            if (!empty($link['new_window'])) {
                $title .= ' 🔗';
            }
            
            $meta = array();
            if (!empty($link['new_window'])) {
                $meta['target'] = '_blank';
            }
            
            $wp_admin_bar->add_menu(array(
                'id' => 'qlab-quick-link-' . $index,
                'parent' => 'qlab-quick-links',
                'title' => $title,
                'href' => $link['url'],
                'meta' => $meta
            ));
        }
    }
    
    /**
     * Save quick link via AJAX
     */
    public function save_quick_link_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'qlab_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $title = sanitize_text_field($_POST['link_title']);
        $url = esc_url_raw($_POST['link_url']);
        $new_window = isset($_POST['link_new_window']) && $_POST['link_new_window'] === '1';
        
        if (empty($title) || empty($url)) {
            wp_send_json_error('Title and URL are required');
        }
        
        $quick_links = get_option('qlab_quick_links', array());
        $quick_links[] = array(
            'title' => $title,
            'url' => $url,
            'new_window' => $new_window
        );
        
        update_option('qlab_quick_links', $quick_links);
        
        wp_send_json_success('Quick link added successfully');
    }
    
    /**
     * Delete quick link via AJAX
     */
    public function delete_quick_link_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'qlab_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $index = intval($_POST['index']);
        $quick_links = get_option('qlab_quick_links', array());
        
        if (!isset($quick_links[$index])) {
            wp_send_json_error('Quick link not found');
        }
        
        unset($quick_links[$index]);
        $quick_links = array_values($quick_links); // Reindex array
        
        update_option('qlab_quick_links', $quick_links);
        
        wp_send_json_success('Quick link deleted successfully');
    }
    
    /**
     * Update quick link via AJAX
     */
    public function update_quick_link_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'qlab_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $index = intval($_POST['index']);
        $title = sanitize_text_field($_POST['title']);
        $url = esc_url_raw($_POST['url']);
        $new_window = isset($_POST['new_window']) && $_POST['new_window'] === '1';
        
        if (empty($title) || empty($url)) {
            wp_send_json_error('Title and URL are required');
        }
        
        $quick_links = get_option('qlab_quick_links', array());
        
        if (!isset($quick_links[$index])) {
            wp_send_json_error('Quick link not found');
        }
        
        $quick_links[$index] = array(
            'title' => $title,
            'url' => $url,
            'new_window' => $new_window
        );
        
        update_option('qlab_quick_links', $quick_links);
        
        wp_send_json_success('Quick link updated successfully');
    }
    
    /**
     * Reorder quick links via AJAX
     */
    public function reorder_quick_links_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'qlab_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }

        $order = json_decode(stripslashes($_POST['order']), true);
        
        if (!is_array($order)) {
            wp_send_json_error('Invalid order data');
        }
        
        $quick_links = get_option('qlab_quick_links', array());
        $reordered_links = array();
        
        foreach ($order as $index) {
            if (isset($quick_links[$index])) {
                $reordered_links[] = $quick_links[$index];
            }
        }
        
        update_option('qlab_quick_links', $reordered_links);
        
        wp_send_json_success('Order updated successfully');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        add_option('qlab_version', QLAB_VERSION);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
}

// Initialize the plugin
function quick_links_admin_bar() {
    return QuickLinksAdminBar::instance();
}

// Start the plugin
quick_links_admin_bar();
