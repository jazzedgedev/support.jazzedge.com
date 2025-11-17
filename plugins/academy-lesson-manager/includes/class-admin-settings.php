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
        
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('alm_settings', 'alm_membership_levels');
        register_setting('alm_settings', 'alm_keap_tags');
        register_setting('alm_settings', 'alm_keap_blocking_tags');
        register_setting('alm_settings', 'alm_bunny_library_id');
        register_setting('alm_settings', 'alm_bunny_api_key');
        register_setting('alm_settings', 'alm_pathways');
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
        
        // Handle pathway management (in AI tab) - check delete/add first to prevent conflicts
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pathway'])) {
            $this->delete_pathway();
            return; // Exit early after delete
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pathways'])) {
            $this->save_pathways();
            return; // Exit early after save
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_pathway']) || (isset($_POST['new_pathway_key']) && isset($_POST['new_pathway_name'])))) {
            $this->delete_pathway(); // Handles both delete and add
            return; // Exit early after add
        }
        
        // Handle tag management (add/delete)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
            $this->add_tag();
            return; // Exit early after add
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tag'])) {
            $this->delete_tag();
            return; // Exit early after delete
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
                case 'error':
                    echo '<div class="notice notice-error"><p>' . __('An error occurred while saving pathways.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_added':
                    echo '<div class="notice notice-success"><p>' . __('Tag added successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_deleted':
                    echo '<div class="notice notice-success"><p>' . __('Tag deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_exists':
                    echo '<div class="notice notice-error"><p>' . __('A tag with this name already exists.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_in_use':
                    echo '<div class="notice notice-error"><p>' . __('Cannot delete tag: it is assigned to one or more lessons.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_name_required':
                    echo '<div class="notice notice-error"><p>' . __('Tag name is required.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'tag_add_error':
                case 'tag_delete_error':
                case 'invalid_tag':
                case 'tag_not_found':
                    echo '<div class="notice notice-error"><p>' . __('An error occurred while processing the tag.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        $this->render_navigation_buttons();
        
        // Add tabs for settings sections
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        echo '<nav class="nav-tab-wrapper">';
        echo '<a href="?page=academy-manager-settings&tab=general" class="nav-tab ' . ($current_tab === 'general' ? 'nav-tab-active' : '') . '">' . __('General', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=ai" class="nav-tab ' . ($current_tab === 'ai' ? 'nav-tab-active' : '') . '">' . __('AI Settings', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=tags" class="nav-tab ' . ($current_tab === 'tags' ? 'nav-tab-active' : '') . '">' . __('Tags', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=memberships" class="nav-tab ' . ($current_tab === 'memberships' ? 'nav-tab-active' : '') . '">' . __('Memberships', 'academy-lesson-manager') . '</a>';
        echo '</nav>';
        
        if ($current_tab === 'ai') {
            $this->render_ai_settings();
        } elseif ($current_tab === 'tags') {
            $this->render_tags_settings();
        } elseif ($current_tab === 'memberships') {
            $membership_pricing = new ALM_Admin_Membership_Pricing();
            $membership_pricing->render_tab();
        } else {
            $this->render_keap_tags_settings();
            $this->render_bunny_api_settings();
            $this->render_database_update_section();
            $this->render_sync_section();
        }
        
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
        
        // Render blocking tags settings
        $this->render_blocking_tags_settings();
    }
    
    /**
     * Render blocking tags settings
     */
    private function render_blocking_tags_settings() {
        $blocking_tags = get_option('alm_keap_blocking_tags', '');
        
        echo '<div class="alm-settings-section" style="margin-top: 30px;">';
        echo '<h2>' . __('Blocking Tags', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Enter comma-separated Keap tag IDs that should block access to all content. Users with any of these tags will be denied access regardless of their membership level.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="blocking_tags">' . __('Blocking Tag IDs', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="blocking_tags" name="blocking_tags" value="' . esc_attr($blocking_tags) . '" class="regular-text" placeholder="e.g., 7772,8888" />';
        echo '<p class="description">' . __('Comma-separated tag IDs. Users with any of these tags will be denied access.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_settings" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Blocking Tags', 'academy-lesson-manager') . '" />';
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
        
        if (isset($_POST['keap_tags'])) {
            update_option('alm_keap_tags', $_POST['keap_tags']);
        }
        
        // Save blocking tags
        if (isset($_POST['blocking_tags'])) {
            update_option('alm_keap_blocking_tags', sanitize_text_field($_POST['blocking_tags']));
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
    
    /**
     * Render AI Settings page
     */
    private function render_ai_settings() {
        // Get current pathways
        $pathways = get_option('alm_pathways', array());
        
        // If no pathways exist, initialize with defaults
        if (empty($pathways)) {
            $pathways = array(
                'arranging' => 'Arranging',
                'advanced_improv' => 'Advanced Improv',
                'ballads' => 'Ballads',
                'bebop' => 'Bebop',
                'beginner_improv' => 'Beginner Improv',
                'blues' => 'Blues',
                'chord_voicings' => 'Chord Voicings',
                'comping' => 'Comping',
                'ear_training' => 'Ear Training',
                'improvisation' => 'Improvisation',
                'intermediate_improv' => 'Intermediate Improv',
                'jazz_history' => 'Jazz History',
                'latin' => 'Latin',
                'learning_standards' => 'Learning Standards',
                'performance' => 'Performance',
                'practice_techniques' => 'Practice Techniques',
                'reharmonization' => 'Reharmonization',
                'rhythm' => 'Rhythm',
                'scales' => 'Scales',
                'solo_piano' => 'Solo Piano',
                'standards' => 'Standards',
                'technique' => 'Technique',
                'theory' => 'Theory',
                'transcription' => 'Transcription'
            );
            update_option('alm_pathways', $pathways);
        } else {
            // Sort pathways alphabetically by name for display
            asort($pathways);
        }
        
        echo '<div style="margin-top: 20px;">';
        echo '<h2>' . __('AI Pathways Management', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Manage the AI pathways used for lesson recommendations. Each pathway can be assigned to lessons with a rank from 1-5 (1 = most important to recommend).', 'academy-lesson-manager') . '</p>';
        
        // Existing pathways
        echo '<form method="post" action="">';
        echo '<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" style="width: 20%;">' . __('Pathway Key', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" style="width: 50%;">' . __('Pathway Name', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col" style="width: 20%;">' . __('Actions', 'academy-lesson-manager') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($pathways as $key => $name) {
            echo '<tr>';
            echo '<td><code>' . esc_html($key) . '</code></td>';
            echo '<td><input type="text" name="pathways[' . esc_attr($key) . ']" value="' . esc_attr($name) . '" class="regular-text" /></td>';
            echo '<td>';
            echo '<button type="submit" name="delete_pathway" value="' . esc_attr($key) . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to delete this pathway?', 'academy-lesson-manager') . '\');" style="color: #dc3232;">' . __('Delete', 'academy-lesson-manager') . '</button>';
            // Add hidden field to preserve pathways data when deleting
            echo '<input type="hidden" name="pathways[' . esc_attr($key) . ']" value="' . esc_attr($name) . '" />';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        wp_nonce_field('alm_save_pathways', 'alm_pathways_nonce');
        echo '<input type="hidden" name="save_pathways" value="1" />';
        echo '<p class="submit">';
        echo '<input type="submit" class="button-primary" value="' . __('Save Pathways', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        
        // Add new pathway form
        echo '<h3 style="margin-top: 30px;">' . __('Add New Pathway', 'academy-lesson-manager') . '</h3>';
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="new_pathway_name">' . __('Pathway Name', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="new_pathway_name" name="new_pathway_name" value="" class="regular-text" placeholder="e.g., Modal Improv" />';
        echo '<p class="description">' . __('Display name for the pathway. The Pathway Key will be auto-generated from this name.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="new_pathway_key">' . __('Pathway Key', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="new_pathway_key" name="new_pathway_key" value="" class="regular-text" placeholder="Auto-generated from pathway name" readonly />';
        echo '<p class="description">' . __('Automatically generated from pathway name (lowercase, underscores only)', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        wp_nonce_field('alm_add_pathway', 'alm_add_pathway_nonce');
        echo '<input type="hidden" name="add_pathway" value="1" />';
        echo '<p class="submit">';
        echo '<input type="submit" class="button button-primary" value="' . __('Add Pathway', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        
        // JavaScript to auto-generate pathway key from pathway name
        echo '<script>
        jQuery(document).ready(function($) {
            function generatePathwayKey(name) {
                if (!name) return "";
                // Convert to lowercase
                var key = name.toLowerCase();
                // Replace spaces and hyphens with underscores
                key = key.replace(/[\s-]+/g, "_");
                // Remove special characters, keep only alphanumeric and underscores
                key = key.replace(/[^a-z0-9_]/g, "");
                // Remove multiple consecutive underscores
                key = key.replace(/_+/g, "_");
                // Remove leading/trailing underscores
                key = key.replace(/^_+|_+$/g, "");
                return key;
            }
            
            $("#new_pathway_name").on("input", function() {
                var pathwayName = $(this).val();
                var pathwayKey = generatePathwayKey(pathwayName);
                $("#new_pathway_key").val(pathwayKey);
            });
        });
        </script>';
        
        echo '</div>';
    }
    
    /**
     * Save pathways
     */
    private function save_pathways() {
        // Verify nonce
        if (!isset($_POST['alm_pathways_nonce']) || !wp_verify_nonce($_POST['alm_pathways_nonce'], 'alm_save_pathways')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        // Initialize pathways array
        $pathways = array();
        
        // Process pathways if they exist in POST
        if (isset($_POST['pathways']) && is_array($_POST['pathways'])) {
            foreach ($_POST['pathways'] as $key => $name) {
                // Sanitize key and name
                $key = sanitize_key($key);
                $name = sanitize_text_field($name);
                // Only add if both key and name are not empty
                if (!empty($key) && !empty($name)) {
                    $pathways[$key] = $name;
                }
            }
        }
        
        // Sort pathways alphabetically by name
        asort($pathways);
        
        // Always save the pathways array (even if empty, to clear all pathways)
        $result = update_option('alm_pathways', $pathways);
        
        // Redirect with appropriate message
        if ($result !== false) {
            wp_redirect(add_query_arg('message', 'settings_saved', admin_url('admin.php?page=academy-manager-settings&tab=ai')));
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=academy-manager-settings&tab=ai')));
        }
        exit;
    }
    
    /**
     * Handle pathway deletion or addition
     */
    private function delete_pathway() {
        // Handle deletion
        if (isset($_POST['delete_pathway']) && isset($_POST['alm_pathways_nonce']) && wp_verify_nonce($_POST['alm_pathways_nonce'], 'alm_save_pathways')) {
            $key_to_delete = sanitize_key($_POST['delete_pathway']);
            $pathways = get_option('alm_pathways', array());
            if (isset($pathways[$key_to_delete])) {
                unset($pathways[$key_to_delete]);
                update_option('alm_pathways', $pathways);
            }
            wp_redirect(add_query_arg('message', 'settings_saved', admin_url('admin.php?page=academy-manager-settings&tab=ai')));
            exit;
        }
        
        // Handle adding new pathway - check by presence of new_pathway fields OR add_pathway button
        if ((isset($_POST['add_pathway']) || (isset($_POST['new_pathway_key']) && isset($_POST['new_pathway_name']))) && isset($_POST['alm_add_pathway_nonce']) && wp_verify_nonce($_POST['alm_add_pathway_nonce'], 'alm_add_pathway')) {
            $new_key = sanitize_key($_POST['new_pathway_key']);
            $new_name = sanitize_text_field($_POST['new_pathway_name']);
            
            if (!empty($new_key) && !empty($new_name)) {
                $pathways = get_option('alm_pathways', array());
                $pathways[$new_key] = $new_name;
                
                // Sort pathways alphabetically by name
                asort($pathways);
                
                $result = update_option('alm_pathways', $pathways);
                
                wp_redirect(add_query_arg('message', 'settings_saved', admin_url('admin.php?page=academy-manager-settings&tab=ai')));
                exit;
            }
        }
    }
    
    /**
     * Get all pathways (static method for use elsewhere)
     */
    public static function get_pathways() {
        $pathways = get_option('alm_pathways', array());
        if (empty($pathways)) {
            // Return defaults if none set (alphabetically sorted)
            $pathways = array(
                'arranging' => 'Arranging',
                'advanced_improv' => 'Advanced Improv',
                'ballads' => 'Ballads',
                'bebop' => 'Bebop',
                'beginner_improv' => 'Beginner Improv',
                'blues' => 'Blues',
                'chord_voicings' => 'Chord Voicings',
                'comping' => 'Comping',
                'ear_training' => 'Ear Training',
                'improvisation' => 'Improvisation',
                'intermediate_improv' => 'Intermediate Improv',
                'jazz_history' => 'Jazz History',
                'latin' => 'Latin',
                'learning_standards' => 'Learning Standards',
                'performance' => 'Performance',
                'practice_techniques' => 'Practice Techniques',
                'reharmonization' => 'Reharmonization',
                'rhythm' => 'Rhythm',
                'scales' => 'Scales',
                'solo_piano' => 'Solo Piano',
                'standards' => 'Standards',
                'technique' => 'Technique',
                'theory' => 'Theory',
                'transcription' => 'Transcription'
            );
        } else {
            // Sort pathways alphabetically by name
            asort($pathways);
        }
        return $pathways;
    }
    
    /**
     * Render Tags Settings page
     */
    private function render_tags_settings() {
        $tags_table = $this->database->get_table_name('tags');
        $lessons_table = $this->database->get_table_name('lessons');
        
        // Get all tags from tags table
        $tags = $this->wpdb->get_results("
            SELECT t.*, COUNT(DISTINCT l.ID) as lesson_count
            FROM {$tags_table} t
            LEFT JOIN {$lessons_table} l ON (
                l.lesson_tags = t.tag_name 
                OR l.lesson_tags LIKE CONCAT(t.tag_name, ',%')
                OR l.lesson_tags LIKE CONCAT('%, ', t.tag_name, ',%')
                OR l.lesson_tags LIKE CONCAT('%, ', t.tag_name)
            )
            WHERE l.lesson_tags IS NOT NULL AND l.lesson_tags != ''
            GROUP BY t.ID
            ORDER BY lesson_count DESC, t.tag_name ASC
        ");
        
        // Also get tags with 0 count (not used by any lessons)
        $tags_zero = $this->wpdb->get_results("
            SELECT t.*, 0 as lesson_count
            FROM {$tags_table} t
            WHERE t.ID NOT IN (
                SELECT DISTINCT t2.ID
                FROM {$tags_table} t2
                INNER JOIN {$lessons_table} l ON (
                    l.lesson_tags = t2.tag_name 
                    OR l.lesson_tags LIKE CONCAT(t2.tag_name, ',%')
                    OR l.lesson_tags LIKE CONCAT('%, ', t2.tag_name, ',%')
                    OR l.lesson_tags LIKE CONCAT('%, ', t2.tag_name)
                )
                WHERE l.lesson_tags IS NOT NULL AND l.lesson_tags != ''
            )
            ORDER BY t.tag_name ASC
        ");
        
        // Merge and sort
        $all_tags = array_merge($tags, $tags_zero);
        usort($all_tags, function($a, $b) {
            if ($b->lesson_count === $a->lesson_count) {
                return strcmp($a->tag_name, $b->tag_name);
            }
            return $b->lesson_count - $a->lesson_count;
        });
        
        echo '<div style="margin-top: 20px;">';
        echo '<h2>' . __('Lesson Tags Management', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Manage lesson tags. Add new tags or delete tags that are not assigned to any lessons.', 'academy-lesson-manager') . '</p>';
        
        // Add new tag form
        echo '<div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">';
        echo '<h3 style="margin-top: 0;">' . __('Add New Tag', 'academy-lesson-manager') . '</h3>';
        echo '<form method="post" action="" style="display: flex; gap: 12px; align-items: flex-end;">';
        echo '<div style="flex: 1;">';
        echo '<label for="new_tag_name" style="display: block; margin-bottom: 6px; font-weight: 600;">' . __('Tag Name', 'academy-lesson-manager') . '</label>';
        echo '<input type="text" id="new_tag_name" name="new_tag_name" value="" class="regular-text" required style="width: 100%; max-width: 400px;" />';
        echo '<p class="description" style="margin-top: 4px;">' . __('Enter a tag name. The tag slug will be automatically generated.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        echo '<input type="hidden" name="add_tag" value="1" />';
        echo wp_nonce_field('alm_add_tag', 'alm_add_tag_nonce', true, false);
        echo '<input type="submit" class="button button-primary" value="' . __('Add Tag', 'academy-lesson-manager') . '" />';
        echo '</form>';
        echo '</div>';
        
        // Tags list
        echo '<div style="margin-top: 30px;">';
        echo '<h3>' . __('All Tags', 'academy-lesson-manager') . '</h3>';
        
        if (empty($all_tags)) {
            echo '<p>' . __('No tags found. Add your first tag above.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 50%;">' . __('Tag Name', 'academy-lesson-manager') . '</th>';
            echo '<th style="width: 20%;">' . __('Tag Slug', 'academy-lesson-manager') . '</th>';
            echo '<th style="width: 15%;">' . __('Lessons Count', 'academy-lesson-manager') . '</th>';
            echo '<th style="width: 15%;">' . __('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($all_tags as $tag_obj) {
                $tag_name = $tag_obj->tag_name;
                $tag_slug = $tag_obj->tag_slug;
                $count = intval($tag_obj->lesson_count);
                $filter_url = admin_url('admin.php?page=academy-manager-lessons&tag=' . urlencode($tag_name));
                
                echo '<tr>';
                echo '<td><strong>' . esc_html($tag_name) . '</strong></td>';
                echo '<td><code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 12px;">' . esc_html($tag_slug) . '</code></td>';
                echo '<td>' . number_format($count) . '</td>';
                echo '<td>';
                echo '<a href="' . esc_url($filter_url) . '" class="button button-small" style="margin-right: 6px;">' . __('View Lessons', 'academy-lesson-manager') . '</a>';
                if ($count === 0) {
                    echo '<form method="post" action="" style="display: inline-block;" onsubmit="return confirm(\'' . sprintf(__('Are you sure you want to delete the tag "%s"? This action cannot be undone.', 'academy-lesson-manager'), esc_js($tag_name)) . '\');">';
                    echo '<input type="hidden" name="delete_tag" value="' . esc_attr($tag_obj->ID) . '" />';
                    echo wp_nonce_field('alm_delete_tag', 'alm_delete_tag_nonce', true, false);
                    echo '<input type="submit" class="button button-small button-link-delete" value="' . __('Delete', 'academy-lesson-manager') . '" style="color: #dc3232;" />';
                    echo '</form>';
                } else {
                    echo '<span style="color: #999; font-size: 12px;" title="' . esc_attr(sprintf(__('Cannot delete: %d lesson(s) use this tag', 'academy-lesson-manager'), $count)) . '">' . __('In Use', 'academy-lesson-manager') . '</span>';
                }
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Migrate tags from postmeta to lesson_tags column
     */
    private function migrate_tags_from_postmeta() {
        // Check nonce
        if (!isset($_POST['alm_migrate_tags_nonce']) || !wp_verify_nonce($_POST['alm_migrate_tags_nonce'], 'alm_migrate_tags')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $lessons_table = $this->database->get_table_name('lessons');
        $postmeta_table = $this->wpdb->prefix . 'postmeta';
        
        // Get all lesson_tags from postmeta (excluding the _lesson_tags meta_key)
        $results = $this->wpdb->get_results($this->wpdb->prepare("
            SELECT pm.post_id, pm.meta_value
            FROM {$postmeta_table} pm
            WHERE pm.meta_key = %s
            AND pm.meta_value LIKE %s
            AND pm.meta_value != ''
        ", 'lesson_tags', 'a:%'));
        
        if (empty($results)) {
            wp_redirect(add_query_arg('message', 'tags_migration_error', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $tags_table = $this->database->get_table_name('tags');
        $all_unique_tags = array(); // Track all unique tags to populate tags table
        
        foreach ($results as $row) {
            $post_id = intval($row->post_id);
            $serialized_tags = $row->meta_value;
            
            // Unserialize the PHP array
            $tags_array = maybe_unserialize($serialized_tags);
            
            if (!is_array($tags_array) || empty($tags_array)) {
                $skipped++;
                continue;
            }
            
            // Convert array to comma-separated string
            // Remove any empty values and trim whitespace
            $tags_array = array_filter(array_map('trim', $tags_array));
            $tags_string = implode(', ', $tags_array);
            
            // Track unique tags for tags table
            foreach ($tags_array as $tag) {
                $tag = trim($tag);
                if (!empty($tag) && !isset($all_unique_tags[$tag])) {
                    $all_unique_tags[$tag] = true;
                }
            }
            
            // Sanitize and limit to 500 characters (column limit)
            $tags_string = sanitize_text_field($tags_string);
            if (strlen($tags_string) > 500) {
                $tags_string = substr($tags_string, 0, 497) . '...';
            }
            
            // Update the lesson_tags column in wp_alm_lessons
            // Match by post_id
            $result = $this->wpdb->update(
                $lessons_table,
                array('lesson_tags' => $tags_string),
                array('post_id' => $post_id),
                array('%s'),
                array('%d')
            );
            
            if ($result === false) {
                $errors++;
                error_log("ALM Tags Migration: Failed to update lesson_tags for post_id: {$post_id}");
            } elseif ($result > 0) {
                $updated++;
            } else {
                $skipped++; // No matching lesson found
            }
        }
        
        // Populate tags table with unique tags from migration
        foreach ($all_unique_tags as $tag_name => $val) {
            $tag_slug = sanitize_title($tag_name);
            // Check if tag already exists
            $existing = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT ID FROM {$tags_table} WHERE LOWER(tag_name) = LOWER(%s) OR tag_slug = %s",
                $tag_name,
                $tag_slug
            ));
            
            if (!$existing) {
                $this->wpdb->insert(
                    $tags_table,
                    array(
                        'tag_name' => $tag_name,
                        'tag_slug' => $tag_slug
                    ),
                    array('%s', '%s')
                );
            }
        }
        
        // Redirect with success message
        $redirect_url = add_query_arg(
            array(
                'message' => 'tags_migrated',
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors
            ),
            admin_url('admin.php?page=academy-manager-settings&tab=tags')
        );
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Add a new tag
     */
    private function add_tag() {
        // Check nonce
        if (!isset($_POST['alm_add_tag_nonce']) || !wp_verify_nonce($_POST['alm_add_tag_nonce'], 'alm_add_tag')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $tag_name = isset($_POST['new_tag_name']) ? trim(sanitize_text_field($_POST['new_tag_name'])) : '';
        
        if (empty($tag_name)) {
            wp_redirect(add_query_arg('message', 'tag_name_required', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        $tags_table = $this->database->get_table_name('tags');
        
        // Generate slug from tag name
        $tag_slug = sanitize_title($tag_name);
        
        // Check if tag name already exists (case-insensitive)
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT ID FROM {$tags_table} WHERE LOWER(tag_name) = LOWER(%s) OR tag_slug = %s",
            $tag_name,
            $tag_slug
        ));
        
        if ($existing) {
            wp_redirect(add_query_arg('message', 'tag_exists', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        // Insert new tag
        $result = $this->wpdb->insert(
            $tags_table,
            array(
                'tag_name' => $tag_name,
                'tag_slug' => $tag_slug
            ),
            array('%s', '%s')
        );
        
        if ($result === false) {
            wp_redirect(add_query_arg('message', 'tag_add_error', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        wp_redirect(add_query_arg('message', 'tag_added', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
        exit;
    }
    
    /**
     * Delete a tag
     */
    private function delete_tag() {
        // Check nonce
        if (!isset($_POST['alm_delete_tag_nonce']) || !wp_verify_nonce($_POST['alm_delete_tag_nonce'], 'alm_delete_tag')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $tag_id = isset($_POST['delete_tag']) ? intval($_POST['delete_tag']) : 0;
        
        if ($tag_id <= 0) {
            wp_redirect(add_query_arg('message', 'invalid_tag', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        $tags_table = $this->database->get_table_name('tags');
        $lessons_table = $this->database->get_table_name('lessons');
        
        // Get tag info
        $tag = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$tags_table} WHERE ID = %d",
            $tag_id
        ));
        
        if (!$tag) {
            wp_redirect(add_query_arg('message', 'tag_not_found', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        // Check if tag is used by any lessons
        $tag_name = $tag->tag_name;
        $used_count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$lessons_table} 
            WHERE lesson_tags IS NOT NULL AND lesson_tags != '' 
            AND (
                lesson_tags = %s 
                OR lesson_tags LIKE %s 
                OR lesson_tags LIKE %s 
                OR lesson_tags LIKE %s
            )",
            $tag_name,
            $tag_name . ',%',
            '%, ' . $tag_name . ',%',
            '%, ' . $tag_name
        ));
        
        if ($used_count > 0) {
            wp_redirect(add_query_arg('message', 'tag_in_use', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        // Delete tag
        $result = $this->wpdb->delete(
            $tags_table,
            array('ID' => $tag_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_redirect(add_query_arg('message', 'tag_delete_error', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
            exit;
        }
        
        wp_redirect(add_query_arg('message', 'tag_deleted', admin_url('admin.php?page=academy-manager-settings&tab=tags')));
        exit;
    }
    
}
