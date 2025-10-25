<?php
/**
 * ALM Admin Settings Class
 * 
 * Handles membership settings and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Settings {
    
    private $wpdb;
    private $database;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings menu
     */
    public function add_settings_menu() {
        add_submenu_page(
            'academy-manager',
            __('Settings', 'academy-lesson-manager'),
            __('Settings', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('alm_settings', 'alm_membership_levels');
        register_setting('alm_settings', 'alm_keap_tags');
        register_setting('alm_settings', 'alm_bunny_library_id');
        register_setting('alm_settings', 'alm_bunny_api_key');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
            $this->save_settings();
        }
        
        // Handle database update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_database'])) {
            $this->update_database();
        }
        
        // Handle manual sync
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_all'])) {
            $this->sync_all_content();
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . __('Academy Lesson Manager Settings', 'academy-lesson-manager') . '</h1>';
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'settings_saved':
                    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'database_updated':
                    echo '<div class="notice notice-success"><p>' . __('Database updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'content_synced':
                    echo '<div class="notice notice-success"><p>' . __('All content synced successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        $this->render_navigation_buttons();
        $this->render_membership_levels_settings();
        $this->render_keap_tags_settings();
        $this->render_bunny_api_settings();
        $this->render_database_update_section();
        $this->render_sync_section();
        
        echo '</div>';
    }
    
    /**
     * Render navigation buttons
     */
    private function render_navigation_buttons() {
        echo '<div class="alm-navigation-buttons" style="margin-bottom: 20px;">';
        echo '<a href="?page=academy-manager" class="button">' . __('Collections', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-lessons" class="button">' . __('Lessons', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-chapters" class="button">' . __('Chapters', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings" class="button button-primary" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }
    
    /**
     * Render membership levels settings
     */
    private function render_membership_levels_settings() {
        $membership_levels = get_option('alm_membership_levels', array(
            'free' => array('name' => 'Free', 'numeric' => 0, 'description' => 'Free content'),
            'essentials' => array('name' => 'Essentials', 'numeric' => 1, 'description' => 'Basic membership'),
            'studio' => array('name' => 'Studio', 'numeric' => 2, 'description' => 'Standard membership'),
            'premier' => array('name' => 'Premier', 'numeric' => 3, 'description' => 'Premium membership')
        ));
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Membership Levels', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Configure membership levels and their numeric values. Higher numbers have access to lower levels.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        foreach ($membership_levels as $key => $level) {
            echo '<tr>';
            echo '<th scope="row"><label for="' . $key . '_name">' . ucfirst($key) . ' Level</label></th>';
            echo '<td>';
            echo '<input type="text" id="' . $key . '_name" name="membership_levels[' . $key . '][name]" value="' . esc_attr($level['name']) . '" class="regular-text" />';
            echo '<input type="hidden" name="membership_levels[' . $key . '][numeric]" value="' . esc_attr($level['numeric']) . '" />';
            echo '<p class="description">Numeric Level: ' . $level['numeric'] . '</p>';
            echo '</td>';
            echo '</tr>';
            
            echo '<tr>';
            echo '<th scope="row"><label for="' . $key . '_description">' . ucfirst($key) . ' Description</label></th>';
            echo '<td>';
            echo '<input type="text" id="' . $key . '_description" name="membership_levels[' . $key . '][description]" value="' . esc_attr($level['description']) . '" class="regular-text" />';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_settings" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Membership Levels', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render Keap tags settings
     */
    private function render_keap_tags_settings() {
        $keap_tags = get_option('alm_keap_tags', array(
            'free' => '',
            'essentials' => '10290,10288',
            'studio' => '9954,10136,9807,9827,9819,9956,10136',
            'premier' => '9821,9813,10142'
        ));
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Keap Tag IDs', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Enter comma-separated Keap tag IDs for each membership level.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        foreach ($keap_tags as $level => $tags) {
            echo '<tr>';
            echo '<th scope="row"><label for="keap_tags_' . $level . '">' . ucfirst($level) . ' Tags</label></th>';
            echo '<td>';
            echo '<input type="text" id="keap_tags_' . $level . '" name="keap_tags[' . $level . ']" value="' . esc_attr($tags) . '" class="regular-text" placeholder="e.g., 10290,10288" />';
            echo '<p class="description">' . __('Comma-separated tag IDs', 'academy-lesson-manager') . '</p>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_settings" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Keap Tags', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render Bunny.net API settings
     */
    private function render_bunny_api_settings() {
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Bunny.net API Settings', 'academy-lesson-manager') . '</h2>';
        echo '<p>' . __('Configure your Bunny.net Stream API credentials to automatically fetch video metadata including duration.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="alm_bunny_library_id">' . __('Library ID', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="alm_bunny_library_id" name="alm_bunny_library_id" value="' . esc_attr(get_option('alm_bunny_library_id', '')) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your Bunny.net Stream Library ID. Found in your Bunny.net dashboard.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="alm_bunny_api_key">' . __('API Key', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="password" id="alm_bunny_api_key" name="alm_bunny_api_key" value="' . esc_attr(get_option('alm_bunny_api_key', '')) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your Bunny.net Stream API key. Found in your Bunny.net dashboard under API settings.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Test Connection', 'academy-lesson-manager') . '</th>';
        echo '<td>';
        echo '<button type="button" id="test-bunny-connection" class="button">' . __('Test Connection', 'academy-lesson-manager') . '</button>';
        echo '<button type="button" id="debug-bunny-config" class="button" style="margin-left: 10px;">' . __('Debug Config', 'academy-lesson-manager') . '</button>';
        echo '<div id="bunny-test-result" style="margin-top: 10px;"></div>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_settings" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Bunny.net Settings', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Add JavaScript for testing connection
        echo '<script>
        jQuery(document).ready(function($) {
            $("#test-bunny-connection").on("click", function() {
                var $button = $(this);
                var $result = $("#bunny-test-result");
                
                $button.prop("disabled", true).text("Testing...");
                $result.html("");
                
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "alm_test_bunny_connection",
                        nonce: "' . wp_create_nonce('alm_admin_nonce') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html("<div class=\"notice notice-success inline\"><p>" + response.data.message + "</p></div>");
                        } else {
                            $result.html("<div class=\"notice notice-error inline\"><p>" + response.data + "</p></div>");
                        }
                    },
                    error: function() {
                        $result.html("<div class=\"notice notice-error inline\"><p>Connection test failed.</p></div>");
                    },
                    complete: function() {
                        $button.prop("disabled", false).text("Test Connection");
                    }
                });
            });
            
            $("#debug-bunny-config").on("click", function() {
                var $button = $(this);
                var $result = $("#bunny-test-result");
                
                $button.prop("disabled", true).text("Debugging...");
                $result.html("");
                
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "alm_debug_bunny_config",
                        nonce: "' . wp_create_nonce('alm_admin_nonce') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            var debug = response.data;
                            var html = "<div class=\"notice notice-info inline\"><p><strong>Debug Info:</strong><br>";
                            html += "Library ID: " + debug.library_id_value + "<br>";
                            html += "API Key Length: " + debug.api_key_length + "<br>";
                            html += "API Key Start: " + debug.api_key_start + "<br>";
                            html += "Request URL: " + debug.url + "</p></div>";
                            $result.html(html);
                        } else {
                            $result.html("<div class=\"notice notice-error inline\"><p>" + response.data + "</p></div>");
                        }
                    },
                    error: function() {
                        $result.html("<div class=\"notice notice-error inline\"><p>Debug failed.</p></div>");
                    },
                    complete: function() {
                        $button.prop("disabled", false).text("Debug Config");
                    }
                });
            });
        });
        </script>';
    }
    
    /**
     * Render database update section
     */
    private function render_database_update_section() {
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Database Update', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('If membership levels are not saving properly, click the button below to update the database structure.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        echo '<p class="submit">';
        echo '<input type="hidden" name="update_database" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Update Database', 'academy-lesson-manager') . '" onclick="return confirm(\'' . __('Are you sure you want to update the database? This will add membership_level columns if they don\'t exist.', 'academy-lesson-manager') . '\')" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render sync section
     */
    private function render_sync_section() {
        $sync_stats = $this->get_sync_stats();
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Content Synchronization', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Sync ALM data with WordPress posts and ACF fields.', 'academy-lesson-manager') . '</p>';
        
        echo '<div style="margin-bottom: 20px;">';
        echo '<h3>' . __('Sync Status', 'academy-lesson-manager') . '</h3>';
        echo '<ul>';
        echo '<li>' . sprintf(__('Collections: %d total, %d synced', 'academy-lesson-manager'), $sync_stats['collections_total'], $sync_stats['collections_synced']) . '</li>';
        echo '<li>' . sprintf(__('Lessons: %d total, %d synced', 'academy-lesson-manager'), $sync_stats['lessons_total'], $sync_stats['lessons_synced']) . '</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<form method="post" action="">';
        echo '<p class="submit">';
        echo '<input type="hidden" name="sync_all" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Sync All Content', 'academy-lesson-manager') . '" onclick="return confirm(\'' . __('This will sync all ALM data to WordPress posts. Continue?', 'academy-lesson-manager') . '\')" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Get sync statistics
     */
    private function get_sync_stats() {
        global $wpdb;
        $database = new ALM_Database();
        
        $collections_table = $database->get_table_name('collections');
        $lessons_table = $database->get_table_name('lessons');
        
        $collections_total = $wpdb->get_var("SELECT COUNT(*) FROM $collections_table");
        $collections_synced = $wpdb->get_var("SELECT COUNT(*) FROM $collections_table WHERE post_id > 0");
        
        $lessons_total = $wpdb->get_var("SELECT COUNT(*) FROM $lessons_table");
        $lessons_synced = $wpdb->get_var("SELECT COUNT(*) FROM $lessons_table WHERE post_id > 0");
        
        return array(
            'collections_total' => $collections_total,
            'collections_synced' => $collections_synced,
            'lessons_total' => $lessons_total,
            'lessons_synced' => $lessons_synced
        );
    }
    
    /**
     * Sync all content
     */
    private function sync_all_content() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $sync = new ALM_Post_Sync();
        $database = new ALM_Database();
        
        // Sync all collections
        $collections_table = $database->get_table_name('collections');
        $collections = $this->wpdb->get_results("SELECT ID FROM $collections_table");
        
        foreach ($collections as $collection) {
            $sync->sync_collection_to_post($collection->ID);
        }
        
        // Sync all lessons
        $lessons_table = $database->get_table_name('lessons');
        $lessons = $this->wpdb->get_results("SELECT ID FROM $lessons_table");
        
        foreach ($lessons as $lesson) {
            $sync->sync_lesson_to_post($lesson->ID);
        }
        
        wp_redirect(add_query_arg('message', 'content_synced', admin_url('admin.php?page=academy-manager-settings')));
        exit;
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        if (isset($_POST['membership_levels'])) {
            update_option('alm_membership_levels', $_POST['membership_levels']);
        }
        
        if (isset($_POST['keap_tags'])) {
            update_option('alm_keap_tags', $_POST['keap_tags']);
        }
        
        // Save Bunny.net API settings
        if (isset($_POST['alm_bunny_library_id'])) {
            update_option('alm_bunny_library_id', sanitize_text_field($_POST['alm_bunny_library_id']));
        }
        
        if (isset($_POST['alm_bunny_api_key'])) {
            update_option('alm_bunny_api_key', sanitize_text_field($_POST['alm_bunny_api_key']));
        }
        
        wp_redirect(add_query_arg('message', 'settings_saved', admin_url('admin.php?page=academy-manager-settings')));
        exit;
    }
    
    /**
     * Update database structure
     */
    private function update_database() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $database = new ALM_Database();
        $database->check_and_add_membership_columns();
        
        wp_redirect(add_query_arg('message', 'database_updated', admin_url('admin.php?page=academy-manager-settings')));
        exit;
    }
    
    /**
     * Get membership level name by numeric value
     */
    public static function get_membership_level_name($numeric_level) {
        $membership_levels = get_option('alm_membership_levels', array());
        
        // If no saved levels, use defaults
        if (empty($membership_levels)) {
            $default_levels = array(
                array('numeric' => 0, 'name' => 'Free', 'description' => 'Content accessible to all users.'),
                array('numeric' => 1, 'name' => 'Essentials', 'description' => 'Basic membership level.'),
                array('numeric' => 2, 'name' => 'Studio', 'description' => 'Standard membership level.'),
                array('numeric' => 3, 'name' => 'Premier', 'description' => 'Premium membership level (All Access).'),
            );
            $membership_levels = $default_levels;
        }
        
        foreach ($membership_levels as $level) {
            if ($level['numeric'] == $numeric_level) {
                return $level['name'];
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Get all membership levels
     */
    public static function get_membership_levels() {
        return get_option('alm_membership_levels', array(
            'free' => array('name' => 'Free', 'numeric' => 0, 'description' => 'Free content'),
            'essentials' => array('name' => 'Essentials', 'numeric' => 1, 'description' => 'Basic membership'),
            'studio' => array('name' => 'Studio', 'numeric' => 2, 'description' => 'Standard membership'),
            'premier' => array('name' => 'Premier', 'numeric' => 3, 'description' => 'Premium membership')
        ));
    }
}
