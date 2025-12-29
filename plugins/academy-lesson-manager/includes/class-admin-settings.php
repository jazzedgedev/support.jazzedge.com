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
        register_setting('alm_settings', 'alm_ai_lesson_description_prompt');
        register_setting('alm_settings', 'alm_free_trial_lesson_ids');
        register_setting('alm_settings', 'alm_starter_paid_lesson_ids');
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
        
        // Handle webhook settings save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_webhook_settings'])) {
            $this->save_webhook_settings();
            return; // Exit early after save
        }
        
        // Handle webhook log clear
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_webhook_logs'])) {
            $this->clear_webhook_logs();
            return; // Exit early after clear
        }
        
        // Handle AI prompts settings save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ai_prompts'])) {
            $this->save_ai_prompts();
            return; // Exit early after save
        }
        
        // Handle free trial lessons save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_free_trial_lessons'])) {
            $this->save_free_trial_lessons();
            return; // Exit early after save
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
                case 'webhook_settings_saved':
                    echo '<div class="notice notice-success"><p>' . __('Webhook settings saved successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'webhook_logs_cleared':
                    echo '<div class="notice notice-success"><p>' . __('Webhook logs cleared successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'free_trial_saved':
                    echo '<div class="notice notice-success"><p>' . __('Starter Plan lessons saved successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'popup_settings_saved':
                    echo '<div class="notice notice-success"><p>' . __('Popup settings saved successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        $this->render_navigation_buttons();
        
        // Add tabs for settings sections
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        echo '<nav class="nav-tab-wrapper">';
        echo '<a href="?page=academy-manager-settings&tab=general" class="nav-tab ' . ($current_tab === 'general' ? 'nav-tab-active' : '') . '">' . __('General', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=ai" class="nav-tab ' . ($current_tab === 'ai' ? 'nav-tab-active' : '') . '">' . __('AI Settings', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=ai-prompts" class="nav-tab ' . ($current_tab === 'ai-prompts' ? 'nav-tab-active' : '') . '">' . __('AI Prompts', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=tags" class="nav-tab ' . ($current_tab === 'tags' ? 'nav-tab-active' : '') . '">' . __('Tags', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=memberships" class="nav-tab ' . ($current_tab === 'memberships' ? 'nav-tab-active' : '') . '">' . __('Memberships', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=keap-tags" class="nav-tab ' . ($current_tab === 'keap-tags' ? 'nav-tab-active' : '') . '">' . __('Keap Tags', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=free-trial" class="nav-tab ' . ($current_tab === 'free-trial' ? 'nav-tab-active' : '') . '">' . __('Starter Program', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=popup" class="nav-tab ' . ($current_tab === 'popup' ? 'nav-tab-active' : '') . '">' . __('Popup', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=faqs" class="nav-tab ' . ($current_tab === 'faqs' ? 'nav-tab-active' : '') . '">' . __('FAQs', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=promotions" class="nav-tab ' . ($current_tab === 'promotions' ? 'nav-tab-active' : '') . '">' . __('Promotions', 'academy-lesson-manager') . '</a>';
        echo '<a href="?page=academy-manager-settings&tab=webhook" class="nav-tab ' . ($current_tab === 'webhook' ? 'nav-tab-active' : '') . '">' . __('Webhook', 'academy-lesson-manager') . '</a>';
        echo '</nav>';
        
        // Handle promotional banner actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_banner_action'])) {
            $this->handle_promo_banner_action();
            return;
        }
        
        // Handle quick toggle actions (GET request)
        if (isset($_GET['toggle_banner']) && isset($_GET['banner_id'])) {
            $this->handle_promo_banner_toggle();
            return;
        }
        
        if ($current_tab === 'ai') {
            $this->render_ai_settings();
        } elseif ($current_tab === 'ai-prompts') {
            $this->render_ai_prompts_tab();
        } elseif ($current_tab === 'tags') {
            $this->render_tags_settings();
        } elseif ($current_tab === 'memberships') {
            $membership_pricing = new ALM_Admin_Membership_Pricing();
            $membership_pricing->render_tab();
        } elseif ($current_tab === 'keap-tags') {
            $this->render_keap_tags_settings();
        } elseif ($current_tab === 'free-trial') {
            $this->render_free_trial_settings();
        } elseif ($current_tab === 'popup') {
            $this->render_starter_popup_settings();
        } elseif ($current_tab === 'faqs') {
            $faqs_admin = new ALM_Admin_FAQs();
            $faqs_admin->render_tab();
        } elseif ($current_tab === 'promotions') {
            $this->render_promotions_tab();
        } elseif ($current_tab === 'webhook') {
            $this->render_webhook_tab();
        } else {
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
        $default_tags = array(
            'starter_free' => '9661',
            'starter_paid' => '',
            'essentials' => '10290,10288',
            'studio' => '9954,10136,9807,9827,9819,9956,10136',
            'premier' => '9821,9813,10142'
        );
        
        // Get existing tags and merge with defaults for backward compatibility
        $existing_tags = get_option('alm_keap_tags', array());
        $keap_tags = wp_parse_args($existing_tags, $default_tags);
        
        // Handle migration from old 'free' key to 'starter_free'
        if (isset($keap_tags['free']) && !isset($keap_tags['starter_free'])) {
            $keap_tags['starter_free'] = $keap_tags['free'];
        }
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Keap Tag IDs', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Enter comma-separated Keap tag IDs for each membership level.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        // Academy Starter Free Tags
        echo '<tr>';
        echo '<th scope="row"><label for="keap_tags_starter_free">' . __('Academy Starter Free Tags', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="keap_tags_starter_free" name="keap_tags[starter_free]" value="' . esc_attr($keap_tags['starter_free'] ?? '') . '" class="regular-text" placeholder="e.g., 9661" />';
        echo '<p class="description">' . __('Comma-separated tag IDs', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Academy Starter Paid Tags
        echo '<tr>';
        echo '<th scope="row"><label for="keap_tags_starter_paid">' . __('Academy Starter Paid Tags', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="keap_tags_starter_paid" name="keap_tags[starter_paid]" value="' . esc_attr($keap_tags['starter_paid'] ?? '') . '" class="regular-text" placeholder="e.g., 12345" />';
        echo '<p class="description">' . __('Comma-separated tag IDs', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Essentials, Studio, Premier (keep as-is)
        $other_levels = array('essentials', 'studio', 'premier');
        foreach ($other_levels as $level) {
            echo '<tr>';
            echo '<th scope="row"><label for="keap_tags_' . $level . '">' . ucfirst($level) . ' Tags</label></th>';
            echo '<td>';
            echo '<input type="text" id="keap_tags_' . $level . '" name="keap_tags[' . $level . ']" value="' . esc_attr($keap_tags[$level] ?? '') . '" class="regular-text" placeholder="e.g., 10290,10288" />';
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
            // Sanitize keap tags array
            $keap_tags = array();
            foreach ($_POST['keap_tags'] as $level => $tags) {
                $keap_tags[sanitize_key($level)] = sanitize_text_field($tags);
            }
            update_option('alm_keap_tags', $keap_tags);
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
        
        // Determine redirect tab based on which form was submitted
        $redirect_tab = 'general';
        if (isset($_POST['keap_tags'])) {
            $redirect_tab = 'keap-tags';
        }
        
        wp_redirect(add_query_arg(array('message' => 'settings_saved', 'tab' => $redirect_tab), admin_url('admin.php?page=academy-manager-settings')));
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

    /**
     * Render promotions tab
     */
    private function render_promotions_tab() {
        $banners_table = $this->database->get_table_name('promotional_banners');
        $banners = $this->wpdb->get_results("SELECT * FROM {$banners_table} ORDER BY created_at DESC", ARRAY_A);
        
        $editing_id = isset($_GET['edit_banner']) ? intval($_GET['edit_banner']) : 0;
        $editing_banner = $editing_id ? $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$banners_table} WHERE ID = %d", $editing_id), ARRAY_A) : null;
        
        // Show success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            switch ($message) {
                case 'banner_created':
                    echo '<div class="notice notice-success"><p>' . __('Banner created successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'banner_updated':
                    echo '<div class="notice notice-success"><p>' . __('Banner updated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'banner_deleted':
                    echo '<div class="notice notice-success"><p>' . __('Banner deleted successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'banner_error':
                    echo '<div class="notice notice-error"><p>' . __('An error occurred while saving the banner.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'banner_display_required':
                    echo '<div class="notice notice-error"><p>' . __('Please select at least one display location (Dashboard or Join Page).', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'banner_activated':
                    echo '<div class="notice notice-success"><p>' . __('Banner activated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
                case 'banner_deactivated':
                    echo '<div class="notice notice-success"><p>' . __('Banner deactivated successfully.', 'academy-lesson-manager') . '</p></div>';
                    break;
            }
        }
        
        echo '<div class="alm-promotions-section">';
        echo '<h2>' . __('Promotional Banners', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Create promotional banners that can appear on the Practice Hub dashboard and/or the Join page (pricing table). Banners can be text-based with a headline and button, or image-based using the media library.', 'academy-lesson-manager') . '</p>';
        echo '<p class="description"><strong>' . __('Image Dimensions:', 'academy-lesson-manager') . '</strong> ' . __('Recommended size: 1200x300px (4:1 ratio) for best display on all devices.', 'academy-lesson-manager') . '</p>';
        
        // Banner form
        echo '<div class="postbox" style="margin-top: 20px; padding: 20px;">';
        echo '<h3 style="margin-top: 0;">' . ($editing_banner ? __('Edit Banner', 'academy-lesson-manager') : __('Add New Banner', 'academy-lesson-manager')) . '</h3>';
        
        echo '<form method="post" action="">';
        wp_nonce_field('alm_promo_banner_action', 'alm_promo_banner_nonce');
        echo '<input type="hidden" name="promo_banner_action" value="save" />';
        echo '<input type="hidden" name="banner_id" value="' . esc_attr($editing_banner['ID'] ?? 0) . '" />';
        
        echo '<table class="form-table"><tbody>';
        
        // Banner Type
        $banner_type = $editing_banner['banner_type'] ?? 'text';
        echo '<tr>';
        echo '<th scope="row"><label for="banner_type">' . __('Banner Type', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select id="banner_type" name="banner_type" onchange="toggleBannerFields()">';
        echo '<option value="text" ' . selected('text', $banner_type, false) . '>' . __('Text Banner', 'academy-lesson-manager') . '</option>';
        echo '<option value="image" ' . selected('image', $banner_type, false) . '>' . __('Image Banner', 'academy-lesson-manager') . '</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        // Text Banner Fields
        echo '<tr class="banner-field-text">';
        echo '<th scope="row"><label for="banner_headline">' . __('Headline', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="banner_headline" name="banner_headline" value="' . esc_attr($editing_banner['headline'] ?? '') . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr class="banner-field-text">';
        echo '<th scope="row"><label for="banner_text">' . __('Text Content', 'academy-lesson-manager') . '</label></th>';
        echo '<td><textarea id="banner_text" name="banner_text" rows="3" class="large-text">' . esc_textarea($editing_banner['text_content'] ?? '') . '</textarea></td>';
        echo '</tr>';
        
        echo '<tr class="banner-field-text">';
        echo '<th scope="row"><label for="banner_button_text">' . __('Button Text', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="text" id="banner_button_text" name="banner_button_text" value="' . esc_attr($editing_banner['button_text'] ?? '') . '" class="regular-text" placeholder="' . esc_attr__('Shop Now', 'academy-lesson-manager') . '" /></td>';
        echo '</tr>';
        
        echo '<tr class="banner-field-text">';
        echo '<th scope="row"><label for="banner_button_url">' . __('Button URL', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="url" id="banner_button_url" name="banner_button_url" value="' . esc_attr($editing_banner['button_url'] ?? '') . '" class="regular-text" placeholder="/black-friday" /></td>';
        echo '</tr>';
        
        // Image Banner Fields
        $image_id = $editing_banner['image_id'] ?? 0;
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
        echo '<tr class="banner-field-image" style="display: ' . ($banner_type === 'image' ? 'table-row' : 'none') . ';">';
        echo '<th scope="row"><label for="banner_image_id">' . __('Banner Image', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="hidden" id="banner_image_id" name="banner_image_id" value="' . esc_attr($image_id) . '" />';
        echo '<div id="banner_image_preview" style="margin-bottom: 10px;">';
        if ($image_url) {
            echo '<img src="' . esc_url($image_url) . '" style="max-width: 400px; height: auto; border: 1px solid #ddd; border-radius: 4px;" />';
        }
        echo '</div>';
        echo '<button type="button" class="button" id="banner_image_upload">' . __('Select Image', 'academy-lesson-manager') . '</button> ';
        echo '<button type="button" class="button" id="banner_image_remove" style="' . ($image_id ? '' : 'display: none;') . '">' . __('Remove Image', 'academy-lesson-manager') . '</button>';
        echo '<p class="description">' . __('Recommended: 1200x300px (4:1 ratio)', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Button URL for image banners
        echo '<tr class="banner-field-image" style="display: ' . ($banner_type === 'image' ? 'table-row' : 'none') . ';">';
        echo '<th scope="row"><label for="banner_image_button_url">' . __('Click URL', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="url" id="banner_image_button_url" name="banner_image_button_url" value="' . esc_attr($editing_banner['button_url'] ?? '') . '" class="regular-text" placeholder="/black-friday" /></td>';
        echo '</tr>';
        
        // Date Range
        $start_date = $editing_banner['start_date'] ?? '';
        $end_date = $editing_banner['end_date'] ?? '';
        if ($start_date) {
            $start_date = date('Y-m-d\TH:i', strtotime($start_date));
        }
        if ($end_date) {
            $end_date = date('Y-m-d\TH:i', strtotime($end_date));
        }
        
        echo '<tr>';
        echo '<th scope="row"><label for="banner_start_date">' . __('Start Date', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="datetime-local" id="banner_start_date" name="banner_start_date" value="' . esc_attr($start_date) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="banner_end_date">' . __('End Date', 'academy-lesson-manager') . '</label></th>';
        echo '<td><input type="datetime-local" id="banner_end_date" name="banner_end_date" value="' . esc_attr($end_date) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        // Active Status
        $is_active = isset($editing_banner['is_active']) ? intval($editing_banner['is_active']) : 1;
        echo '<tr>';
        echo '<th scope="row">' . __('Status', 'academy-lesson-manager') . '</th>';
        echo '<td><label><input type="checkbox" name="banner_is_active" value="1" ' . checked(1, $is_active, false) . ' /> ' . __('Active', 'academy-lesson-manager') . '</label></td>';
        echo '</tr>';
        
        // Display Locations
        $show_on_dashboard = isset($editing_banner['show_on_dashboard']) ? intval($editing_banner['show_on_dashboard']) : 0;
        $show_on_join_page = isset($editing_banner['show_on_join_page']) ? intval($editing_banner['show_on_join_page']) : 0;
        echo '<tr>';
        echo '<th scope="row">' . __('Display Location', 'academy-lesson-manager') . '</th>';
        echo '<td>';
        echo '<label style="display: block; margin-bottom: 10px;"><input type="checkbox" name="banner_show_on_dashboard" value="1" class="banner-display-location" ' . checked(1, $show_on_dashboard, false) . ' /> ' . __('Show on Dashboard', 'academy-lesson-manager') . '</label>';
        echo '<label style="display: block;"><input type="checkbox" name="banner_show_on_join_page" value="1" class="banner-display-location" ' . checked(1, $show_on_join_page, false) . ' /> ' . __('Show on Join Page', 'academy-lesson-manager') . '</label>';
        echo '<p class="description">' . __('Select at least one location where this promotion should be displayed.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody></table>';
        
        echo '<p class="submit">';
        echo '<button type="submit" class="button button-primary">' . ($editing_banner ? __('Update Banner', 'academy-lesson-manager') : __('Create Banner', 'academy-lesson-manager')) . '</button> ';
        if ($editing_banner) {
            echo '<a href="' . esc_url(remove_query_arg('edit_banner')) . '" class="button">' . __('Cancel', 'academy-lesson-manager') . '</a>';
        }
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Existing banners list
        if (!empty($banners)) {
            echo '<div class="postbox" style="margin-top: 20px; padding: 20px;">';
            echo '<h3 style="margin-top: 0;">' . __('Existing Banners', 'academy-lesson-manager') . '</h3>';
            echo '<table class="widefat striped">';
            echo '<thead><tr>';
            echo '<th>' . __('Type', 'academy-lesson-manager') . '</th>';
            echo '<th>' . __('Content', 'academy-lesson-manager') . '</th>';
            echo '<th>' . __('Display Location', 'academy-lesson-manager') . '</th>';
            echo '<th>' . __('Date Range', 'academy-lesson-manager') . '</th>';
            echo '<th>' . __('Status', 'academy-lesson-manager') . '</th>';
            echo '<th>' . __('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($banners as $banner) {
                $type_label = $banner['banner_type'] === 'image' ? __('Image', 'academy-lesson-manager') : __('Text', 'academy-lesson-manager');
                $content_preview = '';
                if ($banner['banner_type'] === 'image') {
                    $img_url = $banner['image_id'] ? wp_get_attachment_image_url($banner['image_id'], 'thumbnail') : '';
                    $content_preview = $img_url ? '<img src="' . esc_url($img_url) . '" style="max-width: 100px; height: auto;" />' : __('No image', 'academy-lesson-manager');
                } else {
                    $content_preview = esc_html(mb_substr($banner['headline'] ?? '', 0, 50)) . (mb_strlen($banner['headline'] ?? '') > 50 ? '...' : '');
                }
                
                $start_display = $banner['start_date'] ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($banner['start_date'])) : __('No start date', 'academy-lesson-manager');
                $end_display = $banner['end_date'] ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($banner['end_date'])) : __('No end date', 'academy-lesson-manager');
                $status_label = intval($banner['is_active']) ? __('Active', 'academy-lesson-manager') : __('Inactive', 'academy-lesson-manager');
                
                // Display locations
                $locations = array();
                if (isset($banner['show_on_dashboard']) && intval($banner['show_on_dashboard'])) {
                    $locations[] = __('Dashboard', 'academy-lesson-manager');
                }
                if (isset($banner['show_on_join_page']) && intval($banner['show_on_join_page'])) {
                    $locations[] = __('Join Page', 'academy-lesson-manager');
                }
                $locations_display = !empty($locations) ? implode(', ', $locations) : __('None', 'academy-lesson-manager');
                
                $is_active = intval($banner['is_active']);
                $toggle_text = $is_active ? __('Deactivate', 'academy-lesson-manager') : __('Activate', 'academy-lesson-manager');
                $toggle_url = wp_nonce_url(
                    add_query_arg(array(
                        'toggle_banner' => 1,
                        'banner_id' => intval($banner['ID'])
                    )),
                    'toggle_banner_' . intval($banner['ID']),
                    '_wpnonce'
                );
                
                echo '<tr>';
                echo '<td>' . esc_html($type_label) . '</td>';
                echo '<td>' . $content_preview . '</td>';
                echo '<td>' . esc_html($locations_display) . '</td>';
                echo '<td>' . esc_html($start_display) . ' - ' . esc_html($end_display) . '</td>';
                echo '<td>' . esc_html($status_label) . '</td>';
                echo '<td>';
                echo '<a href="' . esc_url($toggle_url) . '" class="button button-small" style="margin-right: 5px;">' . esc_html($toggle_text) . '</a>';
                echo '<a href="' . esc_url(add_query_arg('edit_banner', intval($banner['ID']))) . '" class="button button-small" style="margin-right: 5px;">' . __('Edit', 'academy-lesson-manager') . '</a> ';
                
                echo '<form method="post" action="" style="display:inline-block;" onsubmit="return confirm(\'' . esc_js(__('Delete this banner?', 'academy-lesson-manager')) . '\');">';
                wp_nonce_field('alm_promo_banner_action', 'alm_promo_banner_nonce');
                echo '<input type="hidden" name="promo_banner_action" value="delete" />';
                echo '<input type="hidden" name="banner_id" value="' . esc_attr($banner['ID']) . '" />';
                echo '<button type="submit" class="button button-small button-link-delete">' . __('Delete', 'academy-lesson-manager') . '</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // JavaScript for banner type toggle and media uploader
        ?>
        <script>
        function toggleBannerFields() {
            var type = document.getElementById('banner_type').value;
            var textFields = document.querySelectorAll('.banner-field-text');
            var imageFields = document.querySelectorAll('.banner-field-image');
            
            if (type === 'text') {
                textFields.forEach(function(field) { field.style.display = 'table-row'; });
                imageFields.forEach(function(field) { field.style.display = 'none'; });
            } else {
                textFields.forEach(function(field) { field.style.display = 'none'; });
                imageFields.forEach(function(field) { field.style.display = 'table-row'; });
            }
        }
        
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $('#banner_image_upload').on('click', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: '<?php echo esc_js(__('Choose Banner Image', 'academy-lesson-manager')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Use this image', 'academy-lesson-manager')); ?>'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#banner_image_id').val(attachment.id);
                    $('#banner_image_preview').html('<img src="' + attachment.url + '" style="max-width: 400px; height: auto; border: 1px solid #ddd; border-radius: 4px;" />');
                    $('#banner_image_remove').show();
                });
                
                mediaUploader.open();
            });
            
            $('#banner_image_remove').on('click', function(e) {
                e.preventDefault();
                $('#banner_image_id').val('');
                $('#banner_image_preview').html('');
                $(this).hide();
            });
            
            // Validate display location checkboxes before form submission
            $('form').on('submit', function(e) {
                var dashboardChecked = $('input[name="banner_show_on_dashboard"]').is(':checked');
                var joinPageChecked = $('input[name="banner_show_on_join_page"]').is(':checked');
                
                if (!dashboardChecked && !joinPageChecked) {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('Please select at least one display location (Dashboard or Join Page).', 'academy-lesson-manager')); ?>');
                    return false;
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Handle promotional banner actions
     */
    private function handle_promo_banner_action() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (!isset($_POST['alm_promo_banner_nonce']) || !wp_verify_nonce($_POST['alm_promo_banner_nonce'], 'alm_promo_banner_action')) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }

        $action = isset($_POST['promo_banner_action']) ? sanitize_text_field($_POST['promo_banner_action']) : '';
        $banners_table = $this->database->get_table_name('promotional_banners');
        $message = 'banner_error';

        switch ($action) {
            case 'save':
                $banner_id = isset($_POST['banner_id']) ? intval($_POST['banner_id']) : 0;
                $banner_type = isset($_POST['banner_type']) ? sanitize_text_field($_POST['banner_type']) : 'text';
                
                // Validate that at least one display location is selected
                $show_on_dashboard = isset($_POST['banner_show_on_dashboard']) ? 1 : 0;
                $show_on_join_page = isset($_POST['banner_show_on_join_page']) ? 1 : 0;
                
                if (!$show_on_dashboard && !$show_on_join_page) {
                    wp_redirect(add_query_arg(array(
                        'page' => 'academy-manager-settings',
                        'tab' => 'promotions',
                        'message' => 'banner_display_required',
                        'edit_banner' => $banner_id
                    ), admin_url('admin.php')));
                    exit;
                }
                
                $data = array(
                    'banner_type' => $banner_type,
                    'headline' => $banner_type === 'text' ? sanitize_text_field($_POST['banner_headline'] ?? '') : '',
                    'text_content' => $banner_type === 'text' ? sanitize_textarea_field($_POST['banner_text'] ?? '') : '',
                    'button_text' => $banner_type === 'text' ? sanitize_text_field($_POST['banner_button_text'] ?? '') : '',
                    'button_url' => esc_url_raw($banner_type === 'text' ? ($_POST['banner_button_url'] ?? '') : ($_POST['banner_image_button_url'] ?? '')),
                    'image_id' => $banner_type === 'image' ? intval($_POST['banner_image_id'] ?? 0) : 0,
                    'start_date' => !empty($_POST['banner_start_date']) ? sanitize_text_field($_POST['banner_start_date']) : null,
                    'end_date' => !empty($_POST['banner_end_date']) ? sanitize_text_field($_POST['banner_end_date']) : null,
                    'is_active' => isset($_POST['banner_is_active']) ? 1 : 0,
                    'show_on_dashboard' => $show_on_dashboard,
                    'show_on_join_page' => $show_on_join_page,
                );
                
                if ($banner_id) {
                    $result = $this->wpdb->update(
                        $banners_table,
                        $data,
                        array('ID' => $banner_id),
                        array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if ($result !== false) {
                        $message = 'banner_updated';
                    }
                } else {
                    $result = $this->wpdb->insert($banners_table, $data, array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d'));
                    if ($result !== false) {
                        $message = 'banner_created';
                    }
                }
                break;

            case 'delete':
                $banner_id = isset($_POST['banner_id']) ? intval($_POST['banner_id']) : 0;
                if ($banner_id) {
                    $result = $this->wpdb->delete($banners_table, array('ID' => $banner_id), array('%d'));
                    if ($result !== false) {
                        $message = 'banner_deleted';
                    }
                }
                break;
        }

        wp_redirect(add_query_arg(array(
            'page' => 'academy-manager-settings',
            'tab' => 'promotions',
            'message' => $message,
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Handle quick toggle of banner active status
     */
    private function handle_promo_banner_toggle() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'toggle_banner_' . intval($_GET['banner_id']))) {
            wp_die(__('Security check failed.', 'academy-lesson-manager'));
        }

        $banner_id = isset($_GET['banner_id']) ? intval($_GET['banner_id']) : 0;
        $banners_table = $this->database->get_table_name('promotional_banners');
        
        if ($banner_id) {
            // Get current status
            $banner = $this->wpdb->get_row($this->wpdb->prepare("SELECT is_active FROM {$banners_table} WHERE ID = %d", $banner_id), ARRAY_A);
            
            if ($banner) {
                $new_status = intval($banner['is_active']) ? 0 : 1;
                $result = $this->wpdb->update(
                    $banners_table,
                    array('is_active' => $new_status),
                    array('ID' => $banner_id),
                    array('%d'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $message = $new_status ? 'banner_activated' : 'banner_deactivated';
                } else {
                    $message = 'banner_error';
                }
            } else {
                $message = 'banner_error';
            }
        } else {
            $message = 'banner_error';
        }

        wp_redirect(add_query_arg(array(
            'page' => 'academy-manager-settings',
            'tab' => 'promotions',
            'message' => $message,
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Render webhook settings tab
     */
    private function render_webhook_tab() {
        require_once ALM_PLUGIN_DIR . 'includes/class-zoom-webhook.php';
        $webhook = new ALM_Zoom_Webhook();
        
        // Get current settings
        $secret = get_option('alm_zoom_webhook_secret', '');
        $auto_migrate = get_option('alm_zoom_webhook_auto_migrate', false);
        $webhook_url = rest_url('alm/v1/zoom-recording');
        
        // Get debug logs
        $debug_logs = $webhook->get_debug_logs();
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Zoom Webhook Settings', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Configure the webhook endpoint for automated Zoom recording processing from Zapier.', 'academy-lesson-manager') . '</p>';
        
        // Webhook URL display
        echo '<div style="background: #f0f0f1; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px;">';
        echo '<h3>' . __('Webhook URL', 'academy-lesson-manager') . '</h3>';
        echo '<p><strong>' . __('Endpoint:', 'academy-lesson-manager') . '</strong></p>';
        echo '<code style="display: block; padding: 10px; background: #fff; border: 1px solid #ccc; margin: 10px 0;">' . esc_html($webhook_url) . '</code>';
        echo '<p class="description">' . __('Use this URL in your Zapier webhook configuration. The endpoint accepts POST requests with form data.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        // Zoom Title Format Documentation
        echo '<div style="background: #e7f5e7; border: 1px solid #46b450; padding: 15px; margin-bottom: 20px;">';
        echo '<h3>' . __('Zoom Recording Title Format', 'academy-lesson-manager') . '</h3>';
        echo '<p><strong>' . __('Important:', 'academy-lesson-manager') . '</strong> ' . __('Your Zoom recording titles must include a special format tag to identify the collection and event type.', 'academy-lesson-manager') . '</p>';
        echo '<p><strong>' . __('Format:', 'academy-lesson-manager') . '</strong></p>';
        echo '<code style="display: block; padding: 10px; background: #fff; border: 1px solid #ccc; margin: 10px 0; font-size: 14px;">{id123|willie-coaching}</code>';
        echo '<ul style="margin-left: 20px; margin-top: 10px;">';
        echo '<li><strong>' . __('Left side (before pipe |):', 'academy-lesson-manager') . '</strong> ' . __('Collection ID - The ID of the ALM collection where the lesson should be added. Example: <code>id123</code> or <code>id191</code>', 'academy-lesson-manager') . '</li>';
        echo '<li><strong>' . __('Right side (after pipe |):', 'academy-lesson-manager') . '</strong> ' . __('Event Type - The zoom identifier that matches the ACF field on your je_event posts. Valid values:', 'academy-lesson-manager') . '</li>';
        echo '<ul style="margin-left: 20px; margin-top: 5px;">';
        echo '<li><code>willie-coaching</code></li>';
        echo '<li><code>willie-special</code></li>';
        echo '<li><code>willie-community</code></li>';
        echo '<li><code>paul-class</code></li>';
        echo '</ul>';
        echo '</ul>';
        echo '<p><strong>' . __('Example Zoom Title:', 'academy-lesson-manager') . '</strong></p>';
        echo '<code style="display: block; padding: 10px; background: #fff; border: 1px solid #ccc; margin: 10px 0; font-size: 14px;">Willie Coaching Session - November 2025 {id191|willie-coaching}</code>';
        echo '<p class="description" style="margin-top: 10px;">' . __('The system will extract collection ID 191 and match events with zoom_identifier "willie-coaching" that occurred within ±2 hours of the recording date.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        
        // Settings form
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="alm_zoom_webhook_secret">' . __('Shared Secret', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="alm_zoom_webhook_secret" name="alm_zoom_webhook_secret" value="' . esc_attr($secret) . '" class="regular-text" />';
        echo '<p class="description">' . __('Secret key that Zapier must include in the "code" field. This validates incoming webhook requests.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="alm_zoom_webhook_auto_migrate">' . __('Auto-Migrate Events', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<label>';
        echo '<input type="checkbox" id="alm_zoom_webhook_auto_migrate" name="alm_zoom_webhook_auto_migrate" value="1" ' . checked($auto_migrate, true, false) . ' />';
        echo ' ' . __('Automatically migrate events to collections when Vimeo ID is added', 'academy-lesson-manager');
        echo '</label>';
        echo '<p class="description">' . __('If enabled, events will be automatically converted to lessons and added to the specified collection when a recording is processed.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_webhook_settings" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Webhook Settings', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        
        // Debug logs section
        echo '<div style="margin-top: 30px;">';
        echo '<h3>' . __('Debug Logs', 'academy-lesson-manager') . '</h3>';
        echo '<p class="description">' . __('Recent webhook processing logs. Click "Copy to Clipboard" to share debug information.', 'academy-lesson-manager') . '</p>';
        
        echo '<div style="margin-bottom: 10px;">';
        echo '<button type="button" id="refresh-logs-btn" class="button">' . __('Refresh Logs', 'academy-lesson-manager') . '</button> ';
        echo '<button type="button" id="clear-logs-btn" class="button">' . __('Clear Logs', 'academy-lesson-manager') . '</button> ';
        echo '<button type="button" id="download-logs-btn" class="button">' . __('Download as JSON', 'academy-lesson-manager') . '</button>';
        echo '</div>';
        
        echo '<div id="webhook-logs-container" style="max-height: 600px; overflow-y: auto; border: 1px solid #ddd; background: #fff; padding: 15px;">';
        if (empty($debug_logs)) {
            echo '<p style="color: #666; font-style: italic;">' . __('No debug logs yet. Webhook activity will appear here.', 'academy-lesson-manager') . '</p>';
        } else {
            foreach ($debug_logs as $index => $log) {
                $log_id = 'log-' . $index;
                $log_json = json_encode($log, JSON_PRETTY_PRINT);
                $success = isset($log['success']) && $log['success'];
                $status_class = $success ? 'notice-success' : 'notice-error';
                
                echo '<div class="notice ' . $status_class . ' inline" style="margin-bottom: 15px; padding: 10px;">';
                echo '<p><strong>' . __('Timestamp:', 'academy-lesson-manager') . '</strong> ' . esc_html($log['timestamp']) . '</p>';
                
                if (isset($log['error'])) {
                    echo '<p><strong>' . __('Error:', 'academy-lesson-manager') . '</strong> <span style="color: #dc3232;">' . esc_html($log['error']) . '</span></p>';
                }
                
                if (isset($log['parsed'])) {
                    echo '<p><strong>' . __('Parsed Data:', 'academy-lesson-manager') . '</strong></p>';
                    echo '<ul style="margin-left: 20px;">';
                    foreach ($log['parsed'] as $key => $value) {
                        echo '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</li>';
                    }
                    echo '</ul>';
                }
                
                if (isset($log['matched_event'])) {
                    echo '<p><strong>' . __('Matched Event:', 'academy-lesson-manager') . '</strong> ID ' . esc_html($log['matched_event']['event_id']) . ' - ' . esc_html($log['matched_event']['event_title']) . '</p>';
                }
                
                if (isset($log['sql_query'])) {
                    echo '<p><strong>' . __('SQL Query:', 'academy-lesson-manager') . '</strong></p>';
                    echo '<pre style="background: #f5f5f5; padding: 10px; margin-top: 5px; overflow-x: auto; font-size: 11px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd;">' . esc_html($log['sql_query']) . '</pre>';
                }
                
                if (isset($log['checked_events']) && !empty($log['checked_events'])) {
                    echo '<p><strong>' . __('Events Checked:', 'academy-lesson-manager') . '</strong> (' . count($log['checked_events']) . ' found)</p>';
                    echo '<table style="width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 11px;">';
                    echo '<thead><tr style="background: #f0f0f1;">';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">ID</th>';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Title</th>';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Event Start (Local)</th>';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Event Start (UTC)</th>';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Recording (UTC)</th>';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Diff (hours)</th>';
                    echo '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Status</th>';
                    echo '</tr></thead><tbody>';
                    foreach ($log['checked_events'] as $checked) {
                        $status_color = isset($checked['within_window']) && $checked['within_window'] ? '#46b450' : '#dc3232';
                        $status_text = isset($checked['within_window']) && $checked['within_window'] ? 'Within ±2h' : (isset($checked['reason']) ? $checked['reason'] : 'Outside ±2h');
                        echo '<tr>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($checked['event_id']) . '</td>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($checked['event_title']) . '</td>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . (isset($checked['event_start_local']) ? esc_html($checked['event_start_local']) : (isset($checked['event_start']) ? esc_html($checked['event_start']) : 'N/A')) . '</td>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . (isset($checked['event_start_utc']) ? esc_html($checked['event_start_utc']) : 'N/A') . '</td>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . (isset($checked['recording_date_utc']) ? esc_html($checked['recording_date_utc']) : 'N/A') . '</td>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd;">' . (isset($checked['diff_hours']) ? esc_html($checked['diff_hours']) : 'N/A') . '</td>';
                        echo '<td style="padding: 8px; border: 1px solid #ddd; color: ' . $status_color . ';">' . esc_html($status_text) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                
                echo '<details style="margin-top: 10px;">';
                echo '<summary style="cursor: pointer; font-weight: bold;">' . __('View Full Debug Info', 'academy-lesson-manager') . '</summary>';
                echo '<pre id="log-pre-' . esc_attr($log_id) . '" style="background: #f5f5f5; padding: 10px; margin-top: 10px; overflow-x: auto; font-size: 12px; max-height: 400px; overflow-y: auto;">' . esc_html($log_json) . '</pre>';
                echo '</details>';
                
                // Store JSON in a hidden textarea for reliable copying
                // Use base64 encoding to avoid any HTML entity issues
                echo '<textarea id="log-json-' . esc_attr($log_id) . '" style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;">' . esc_textarea($log_json) . '</textarea>';
                echo '<button type="button" class="button button-small copy-log-btn" data-log-id="' . esc_attr($log_id) . '" style="margin-top: 10px;">' . __('Copy to Clipboard', 'academy-lesson-manager') . '</button>';
                echo '</div>';
            }
        }
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Enqueue admin script
        wp_enqueue_script('alm-webhook-settings', ALM_PLUGIN_URL . 'assets/js/alm-webhook-settings.js', array('jquery'), ALM_VERSION, true);
        wp_localize_script('alm-webhook-settings', 'almWebhookSettings', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alm_webhook_settings'),
            'restUrl' => rest_url('alm/v1/zoom-recording')
        ));
    }
    
    /**
     * Save webhook settings
     */
    private function save_webhook_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $secret = isset($_POST['alm_zoom_webhook_secret']) ? sanitize_text_field($_POST['alm_zoom_webhook_secret']) : '';
        $auto_migrate = isset($_POST['alm_zoom_webhook_auto_migrate']) ? true : false;
        
        update_option('alm_zoom_webhook_secret', $secret);
        update_option('alm_zoom_webhook_auto_migrate', $auto_migrate);
        
        wp_redirect(add_query_arg(array(
            'page' => 'academy-manager-settings',
            'tab' => 'webhook',
            'message' => 'webhook_settings_saved'
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Clear webhook logs
     */
    private function clear_webhook_logs() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        require_once ALM_PLUGIN_DIR . 'includes/class-zoom-webhook.php';
        $webhook = new ALM_Zoom_Webhook();
        $webhook->clear_debug_logs();
        
        wp_redirect(add_query_arg(array(
            'page' => 'academy-manager-settings',
            'tab' => 'webhook',
            'message' => 'webhook_logs_cleared'
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Render AI Prompts tab
     */
    private function render_ai_prompts_tab() {
        $default_prompt = 'Create a compelling lesson description based on the following transcript. Limit to 100 words or less. No emojis.';
        $current_prompt = get_option('alm_ai_lesson_description_prompt', $default_prompt);
        
        echo '<form method="post" action="">';
        echo '<h2>' . __('AI Prompts', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Configure AI prompts used throughout the Academy Manager. These prompts are used to generate content using OpenAI.', 'academy-lesson-manager') . '</p>';
        
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="alm_ai_lesson_description_prompt">' . __('Lesson Description Prompt', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<textarea id="alm_ai_lesson_description_prompt" name="alm_ai_lesson_description_prompt" rows="5" cols="80" class="large-text">' . esc_textarea($current_prompt) . '</textarea>';
        echo '<p class="description">' . __('This prompt is used when generating lesson descriptions from transcripts. The transcript text will be appended to this prompt.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="save_ai_prompts" class="button button-primary" value="' . __('Save Prompts', 'academy-lesson-manager') . '" />';
        echo '</p>';
        
        wp_nonce_field('alm_save_ai_prompts', 'alm_ai_prompts_nonce');
        echo '</form>';
    }
    
    /**
     * Save AI Prompts settings
     */
    private function save_ai_prompts() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('alm_save_ai_prompts', 'alm_ai_prompts_nonce');
        
        $prompt = isset($_POST['alm_ai_lesson_description_prompt']) ? sanitize_textarea_field($_POST['alm_ai_lesson_description_prompt']) : '';
        
        update_option('alm_ai_lesson_description_prompt', $prompt);
        
        wp_redirect(add_query_arg(array(
            'page' => 'academy-manager-settings',
            'tab' => 'ai-prompts',
            'message' => 'settings_saved'
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Render Free Trial settings tab
     */
    private function render_free_trial_settings() {
        $free_trial_lesson_ids = get_option('alm_free_trial_lesson_ids', array());
        $starter_paid_lesson_ids = get_option('alm_starter_paid_lesson_ids', array());
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Starter Program Lesson Access', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Select lessons that should be accessible to Starter Program users. Free starter users see only Free lessons. Paid starter users see both Free and Paid lessons.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="" id="starter-program-form">';
        
        // FREE SECTION
        echo '<div style="margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid #ddd;">';
        echo '<h3 style="margin-top: 0;">' . __('Free Starter Lessons', 'academy-lesson-manager') . '</h3>';
        echo '<p class="description">' . __('Lessons accessible to users with Academy Starter Free tags.', 'academy-lesson-manager') . '</p>';
        
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="free_trial_lesson_search">' . __('Add Lesson', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div id="free-trial-lesson-picker" style="position: relative;">';
        echo '<input type="text" id="free_trial_lesson_search" class="regular-text" placeholder="' . __('Search for a lesson...', 'academy-lesson-manager') . '" autocomplete="off" style="width: 100%; max-width: 600px;" />';
        echo '<div id="free-trial-lesson-results" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; max-height:300px; overflow-y:auto; z-index:1000; width:100%; max-width:600px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"></div>';
        echo '</div>';
        echo '<p class="description">' . __('Start typing to search for lessons. Click a lesson to add it to the Free Starter list.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Free Starter Lessons', 'academy-lesson-manager') . '</th>';
        echo '<td>';
        echo '<div id="free-trial-lessons-list">';
        
        if (!empty($free_trial_lesson_ids)) {
            global $wpdb;
            $database = new ALM_Database();
            $lessons_table = $database->get_table_name('lessons');
            
            $placeholders = implode(',', array_fill(0, count($free_trial_lesson_ids), '%d'));
            $lessons = $wpdb->get_results($wpdb->prepare(
                "SELECT l.ID, l.lesson_title, l.post_id, c.collection_title 
                 FROM {$lessons_table} l
                 LEFT JOIN {$wpdb->prefix}alm_collections c ON l.collection_id = c.ID
                 WHERE l.post_id IN ($placeholders)
                 ORDER BY l.lesson_title ASC",
                ...$free_trial_lesson_ids
            ));
            
            echo '<ul id="free-trial-lessons-ul" style="list-style:none; padding:0; margin:10px 0;">';
            foreach ($lessons as $lesson) {
                $post_id = intval($lesson->post_id);
                $collection_name = $lesson->collection_title ? $lesson->collection_title : 'No Collection';
                echo '<li style="padding:8px; margin:5px 0; background:#f5f5f5; border-left:3px solid #0073aa;">';
                echo '<span style="font-weight:600;">' . esc_html($lesson->lesson_title) . '</span>';
                echo ' <span style="color:#666; font-size:12px;">(' . esc_html($collection_name) . ')</span>';
                echo ' <input type="hidden" name="free_trial_lesson_ids[]" value="' . $post_id . '" />';
                echo ' <button type="button" class="button-link remove-free-trial-lesson" data-post-id="' . $post_id . '" style="color:#a00; margin-left:10px;">' . __('Remove', 'academy-lesson-manager') . '</button>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color:#666; font-style:italic;">' . __('No lessons added yet. Use the search above to add lessons.', 'academy-lesson-manager') . '</p>';
            echo '<ul id="free-trial-lessons-ul" style="list-style:none; padding:0; margin:10px 0; display:none;"></ul>';
        }
        
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        // PAID SECTION
        echo '<div style="margin-bottom: 40px;">';
        echo '<h3 style="margin-top: 0;">' . __('Paid Starter Lessons', 'academy-lesson-manager') . '</h3>';
        echo '<p class="description">' . __('Additional lessons accessible to users with Academy Starter Paid tags. Paid starter users also have access to all Free starter lessons.', 'academy-lesson-manager') . '</p>';
        
        echo '<table class="form-table">';
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="starter_paid_lesson_search">' . __('Add Lesson', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div id="starter-paid-lesson-picker" style="position: relative;">';
        echo '<input type="text" id="starter_paid_lesson_search" class="regular-text" placeholder="' . __('Search for a lesson...', 'academy-lesson-manager') . '" autocomplete="off" style="width: 100%; max-width: 600px;" />';
        echo '<div id="starter-paid-lesson-results" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; max-height:300px; overflow-y:auto; z-index:1000; width:100%; max-width:600px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"></div>';
        echo '</div>';
        echo '<p class="description">' . __('Start typing to search for lessons. Click a lesson to add it to the Paid Starter list.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Paid Starter Lessons', 'academy-lesson-manager') . '</th>';
        echo '<td>';
        echo '<div id="starter-paid-lessons-list">';
        
        if (!empty($starter_paid_lesson_ids)) {
            global $wpdb;
            $database = new ALM_Database();
            $lessons_table = $database->get_table_name('lessons');
            
            $placeholders = implode(',', array_fill(0, count($starter_paid_lesson_ids), '%d'));
            $lessons = $wpdb->get_results($wpdb->prepare(
                "SELECT l.ID, l.lesson_title, l.post_id, c.collection_title 
                 FROM {$lessons_table} l
                 LEFT JOIN {$wpdb->prefix}alm_collections c ON l.collection_id = c.ID
                 WHERE l.post_id IN ($placeholders)
                 ORDER BY l.lesson_title ASC",
                ...$starter_paid_lesson_ids
            ));
            
            echo '<ul id="starter-paid-lessons-ul" style="list-style:none; padding:0; margin:10px 0;">';
            foreach ($lessons as $lesson) {
                $post_id = intval($lesson->post_id);
                $collection_name = $lesson->collection_title ? $lesson->collection_title : 'No Collection';
                echo '<li style="padding:8px; margin:5px 0; background:#f5f5f5; border-left:3px solid #28a745;">';
                echo '<span style="font-weight:600;">' . esc_html($lesson->lesson_title) . '</span>';
                echo ' <span style="color:#666; font-size:12px;">(' . esc_html($collection_name) . ')</span>';
                echo ' <input type="hidden" name="starter_paid_lesson_ids[]" value="' . $post_id . '" />';
                echo ' <button type="button" class="button-link remove-starter-paid-lesson" data-post-id="' . $post_id . '" style="color:#a00; margin-left:10px;">' . __('Remove', 'academy-lesson-manager') . '</button>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color:#666; font-style:italic;">' . __('No lessons added yet. Use the search above to add lessons.', 'academy-lesson-manager') . '</p>';
            echo '<ul id="starter-paid-lessons-ul" style="list-style:none; padding:0; margin:10px 0; display:none;"></ul>';
        }
        
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_free_trial_lessons" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Starter Program Lessons', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Enqueue the JavaScript for the lesson pickers
        $this->enqueue_free_trial_scripts();
    }
    
    /**
     * Render Starter Popup Marketing Settings
     */
    private function render_starter_popup_settings() {
        // Handle form submission
        if (isset($_POST['save_starter_popup_settings']) && check_admin_referer('alm_save_starter_popup', 'alm_starter_popup_nonce')) {
            $this->save_starter_popup_settings();
        }
        
        // Get current settings
        $popup_enabled = get_option('alm_starter_popup_enabled', '0');
        $popup_style = get_option('alm_starter_popup_style', 'modal'); // modal, banner, both
        $popup_audience = get_option('alm_starter_popup_audience', 'all'); // all, logged_in, logged_out
        $popup_days_before_repeat = get_option('alm_starter_popup_days_before_repeat', 7); // days
        $popup_test_mode = get_option('alm_starter_popup_test_mode', '0');
        $popup_debug_mode = get_option('alm_starter_popup_debug_mode', '0');
        
        // New fields
        $popup_modal_headline = get_option('alm_starter_popup_modal_headline', '');
        $popup_modal_headline_align = get_option('alm_starter_popup_modal_headline_align', 'center');
        $popup_modal_content = stripslashes(get_option('alm_starter_popup_modal_content', ''));
        $popup_modal_delay = get_option('alm_starter_popup_modal_delay', 10); // seconds
        $popup_banner_headline = get_option('alm_starter_popup_banner_headline', '');
        $popup_banner_content = stripslashes(get_option('alm_starter_popup_banner_content', ''));
        $popup_banner_delay = get_option('alm_starter_popup_banner_delay', 10); // seconds
        $popup_cta_text = get_option('alm_starter_popup_cta_text', 'Get Started');
        $popup_button_color = get_option('alm_starter_popup_button_color', '#239B90');
        $popup_button_url = get_option('alm_starter_popup_button_url', '/starter');
        $popup_modal_animation = get_option('alm_starter_popup_modal_animation', 'fade'); // fade, slide
        $popup_banner_animation = get_option('alm_starter_popup_banner_animation', 'slide'); // fade, slide
        $popup_page_target = get_option('alm_starter_popup_page_target', 'any'); // any, specific
        $popup_target_page_ids = get_option('alm_starter_popup_target_page_ids', array()); // array of page IDs
        $popup_exclude_page_ids = get_option('alm_starter_popup_exclude_page_ids', array()); // array of page IDs to exclude
        
        // Backward compatibility: if old single page ID exists, convert to array
        $old_page_id = get_option('alm_starter_popup_target_page_id', '0');
        if ($old_page_id > 0 && empty($popup_target_page_ids)) {
            $popup_target_page_ids = array($old_page_id);
            update_option('alm_starter_popup_target_page_ids', $popup_target_page_ids);
        }
        
        // Ensure arrays
        if (!is_array($popup_target_page_ids)) {
            $popup_target_page_ids = array();
        }
        if (!is_array($popup_exclude_page_ids)) {
            $popup_exclude_page_ids = array();
        }
        
        echo '<div class="alm-settings-section">';
        echo '<h2>' . __('Starter Plan Marketing Popup', 'academy-lesson-manager') . '</h2>';
        echo '<p class="description">' . __('Configure the marketing popup that promotes the Academy Starter program. Use the shortcode <code>[academy_starter_popup]</code> on your homepage or any page.', 'academy-lesson-manager') . '</p>';
        
        echo '<form method="post" action="">';
        wp_nonce_field('alm_save_starter_popup', 'alm_starter_popup_nonce');
        echo '<table class="form-table">';
        echo '<tbody>';
        
        // Enable/Disable
        echo '<tr>';
        echo '<th scope="row"><label for="popup_enabled">' . __('Enable Popup', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<label><input type="checkbox" name="popup_enabled" value="1" ' . checked($popup_enabled, '1', false) . ' /> ' . __('Show the popup on pages where the shortcode is placed', 'academy-lesson-manager') . '</label>';
        echo '</td>';
        echo '</tr>';
        
        // Test Mode
        echo '<tr>';
        echo '<th scope="row"><label for="popup_test_mode">' . __('Test Mode', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<label><input type="checkbox" name="popup_test_mode" value="1" ' . checked($popup_test_mode, '1', false) . ' /> ' . __('Enable test mode - popup will always show regardless of localStorage settings', 'academy-lesson-manager') . '</label>';
        echo '<p class="description">' . __('Use this to test the popup without clearing browser storage. The popup will appear every time the page loads when test mode is enabled.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Debug Mode
        echo '<tr>';
        echo '<th scope="row"><label for="popup_debug_mode">' . __('Debug Mode', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<label><input type="checkbox" name="popup_debug_mode" value="1" ' . checked($popup_debug_mode, '1', false) . ' /> ' . __('Enable debug mode - show console logs even when test mode is off', 'academy-lesson-manager') . '</label>';
        echo '<p class="description">' . __('When enabled, detailed console logs will be shown in the browser console. Useful for debugging popup behavior without enabling test mode.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Popup Style
        echo '<tr>';
        echo '<th scope="row"><label for="popup_style">' . __('Popup Style', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select name="popup_style" id="popup_style">';
        echo '<option value="modal" ' . selected($popup_style, 'modal', false) . '>Modal Overlay</option>';
        echo '<option value="banner" ' . selected($popup_style, 'banner', false) . '>Bottom Banner</option>';
        echo '<option value="both" ' . selected($popup_style, 'both', false) . '>Both (Modal + Banner)</option>';
        echo '</select>';
        echo '<p class="description">' . __('Choose how the popup appears: Modal overlay (centered), bottom banner, or both.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Audience
        echo '<tr>';
        echo '<th scope="row"><label for="popup_audience">' . __('Show To', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select name="popup_audience" id="popup_audience">';
        echo '<option value="all" ' . selected($popup_audience, 'all', false) . '>Everyone</option>';
        echo '<option value="logged_in" ' . selected($popup_audience, 'logged_in', false) . '>Logged In Users Only</option>';
        echo '<option value="logged_out" ' . selected($popup_audience, 'logged_out', false) . '>Logged Out Users Only</option>';
        echo '</select>';
        echo '<p class="description">' . __('Choose who should see the popup.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Modal Delay
        echo '<tr>';
        echo '<th scope="row"><label for="popup_modal_delay">' . __('Modal Show After (seconds)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" name="popup_modal_delay" id="popup_modal_delay" value="' . esc_attr($popup_modal_delay) . '" min="0" step="1" class="small-text" />';
        echo '<p class="description">' . __('How many seconds to wait before showing the modal popup. Default: 10 seconds.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Banner Delay
        echo '<tr>';
        echo '<th scope="row"><label for="popup_banner_delay">' . __('Banner Show After (seconds)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" name="popup_banner_delay" id="popup_banner_delay" value="' . esc_attr($popup_banner_delay) . '" min="0" step="1" class="small-text" />';
        echo '<p class="description">' . __('How many seconds to wait before showing the banner popup. Default: 10 seconds.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Days Before Repeat
        echo '<tr>';
        echo '<th scope="row"><label for="popup_days_before_repeat">' . __('Days Before Showing Again', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="number" name="popup_days_before_repeat" id="popup_days_before_repeat" value="' . esc_attr($popup_days_before_repeat) . '" min="0" step="1" class="small-text" />';
        echo '<p class="description">' . __('After a user closes the popup, how many days before they see it again. Uses localStorage.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Page Targeting - Show On
        echo '<tr>';
        echo '<th scope="row"><label for="popup_page_target">' . __('Show On', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select name="popup_page_target" id="popup_page_target">';
        echo '<option value="any" ' . selected($popup_page_target, 'any', false) . '>Any Page</option>';
        echo '<option value="specific" ' . selected($popup_page_target, 'specific', false) . '>Specific Pages</option>';
        echo '</select>';
        echo '<div id="popup_target_page_wrapper" style="margin-top: 10px; ' . ($popup_page_target === 'specific' ? '' : 'display: none;') . '">';
        
        // Get front page ID
        $front_page_id = get_option('page_on_front', 0);
        
        // Build checkbox list for multiple page selection
        $pages = get_pages(array('sort_column' => 'post_title'));
        echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">';
        foreach ($pages as $page) {
            $checked = in_array($page->ID, $popup_target_page_ids) ? 'checked="checked"' : '';
            $front_page_indicator = ($page->ID == $front_page_id) ? ' <strong>(Front Page)</strong>' : '';
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="popup_target_page_ids[]" value="' . esc_attr($page->ID) . '" ' . $checked . ' /> ';
            echo esc_html($page->post_title) . $front_page_indicator;
            echo '</label>';
        }
        echo '</div>';
        echo '</div>';
        echo '<p class="description">' . __('Choose whether to show the popup on any page or only on specific pages. Select multiple pages by checking the boxes.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Page Targeting - Hide On
        echo '<tr>';
        echo '<th scope="row"><label for="popup_exclude_pages">' . __('Hide On', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">';
        if (empty($pages)) {
            echo '<p>' . __('No pages found.', 'academy-lesson-manager') . '</p>';
        } else {
            foreach ($pages as $page) {
                $checked = in_array($page->ID, $popup_exclude_page_ids) ? 'checked="checked"' : '';
                $front_page_indicator = ($page->ID == $front_page_id) ? ' <strong>(Front Page)</strong>' : '';
                echo '<label style="display: block; margin-bottom: 5px;">';
                echo '<input type="checkbox" name="popup_exclude_page_ids[]" value="' . esc_attr($page->ID) . '" ' . $checked . ' /> ';
                echo esc_html($page->post_title) . $front_page_indicator;
                echo '</label>';
            }
        }
        echo '</div>';
        echo '<p class="description">' . __('Select pages where the popup should NOT appear. This overrides the "Show On" setting.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Modal Headline
        echo '<tr>';
        echo '<th scope="row"><label for="popup_modal_headline">' . __('Modal Headline', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="popup_modal_headline" id="popup_modal_headline" value="' . esc_attr($popup_modal_headline) . '" class="regular-text" placeholder="Choose Your Academy Starter Program" />';
        echo '<p class="description">' . __('Headline for the modal popup.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Modal Headline Alignment
        echo '<tr>';
        echo '<th scope="row"><label for="popup_modal_headline_align">' . __('Modal Headline Alignment', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select name="popup_modal_headline_align" id="popup_modal_headline_align">';
        echo '<option value="left" ' . selected($popup_modal_headline_align, 'left', false) . '>Left</option>';
        echo '<option value="center" ' . selected($popup_modal_headline_align, 'center', false) . '>Center</option>';
        echo '<option value="right" ' . selected($popup_modal_headline_align, 'right', false) . '>Right</option>';
        echo '</select>';
        echo '<p class="description">' . __('Text alignment for the modal headline.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Modal Content
        echo '<tr>';
        echo '<th scope="row"><label for="popup_modal_content">' . __('Modal Content', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        wp_editor($popup_modal_content, 'popup_modal_content', array(
            'textarea_name' => 'popup_modal_content',
            'textarea_rows' => 10,
            'media_buttons' => true,
            'teeny' => false
        ));
        echo '<p class="description">' . __('Content for the modal popup. You can use HTML formatting.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Banner Headline
        echo '<tr>';
        echo '<th scope="row"><label for="popup_banner_headline">' . __('Banner Headline', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="popup_banner_headline" id="popup_banner_headline" value="' . esc_attr($popup_banner_headline) . '" class="regular-text" placeholder="Choose Your Academy Starter Program" />';
        echo '<p class="description">' . __('Headline for the banner popup.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Banner Content
        echo '<tr>';
        echo '<th scope="row"><label for="popup_banner_content">' . __('Banner Content', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        wp_editor($popup_banner_content, 'popup_banner_content', array(
            'textarea_name' => 'popup_banner_content',
            'textarea_rows' => 10,
            'media_buttons' => true,
            'teeny' => false
        ));
        echo '<p class="description">' . __('Content for the banner popup. You can use HTML formatting.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // CTA Button Text
        echo '<tr>';
        echo '<th scope="row"><label for="popup_cta_text">' . __('Call to Action (Button Text)', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="popup_cta_text" id="popup_cta_text" value="' . esc_attr($popup_cta_text) . '" class="regular-text" placeholder="Get Started" />';
        echo '<p class="description">' . __('Text displayed on the call-to-action button.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Button Color
        echo '<tr>';
        echo '<th scope="row"><label for="popup_button_color">' . __('Button Color', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="popup_button_color" id="popup_button_color" value="' . esc_attr($popup_button_color) . '" class="regular-text" style="width: 100px;" />';
        echo '<input type="color" id="popup_button_color_picker" value="' . esc_attr($popup_button_color) . '" style="margin-left: 10px; vertical-align: middle; height: 35px; width: 50px;" />';
        echo '<p class="description">' . __('Color for the call-to-action button. Click the color picker or enter a hex code.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Button URL
        echo '<tr>';
        echo '<th scope="row"><label for="popup_button_url">' . __('Button URL', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="popup_button_url" id="popup_button_url" value="' . esc_attr($popup_button_url) . '" class="regular-text" placeholder="/starter" />';
        echo '<p class="description">' . __('URL where the button links to.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Modal Animation
        echo '<tr>';
        echo '<th scope="row"><label for="popup_modal_animation">' . __('Modal Animation', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select name="popup_modal_animation" id="popup_modal_animation">';
        echo '<option value="fade" ' . selected($popup_modal_animation, 'fade', false) . '>Fade In</option>';
        echo '<option value="slide" ' . selected($popup_modal_animation, 'slide', false) . '>Slide In</option>';
        echo '</select>';
        echo '<p class="description">' . __('Animation style for the modal popup.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Banner Animation
        echo '<tr>';
        echo '<th scope="row"><label for="popup_banner_animation">' . __('Banner Animation', 'academy-lesson-manager') . '</label></th>';
        echo '<td>';
        echo '<select name="popup_banner_animation" id="popup_banner_animation">';
        echo '<option value="fade" ' . selected($popup_banner_animation, 'fade', false) . '>Fade In</option>';
        echo '<option value="slide" ' . selected($popup_banner_animation, 'slide', false) . '>Slide In</option>';
        echo '</select>';
        echo '<p class="description">' . __('Animation style for the banner popup.', 'academy-lesson-manager') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="hidden" name="save_starter_popup_settings" value="1" />';
        echo '<input type="submit" class="button-primary" value="' . __('Save Popup Settings', 'academy-lesson-manager') . '" />';
        echo '</p>';
        echo '</form>';
        echo '</div>';
        
        // Add JavaScript for color picker and page targeting
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Color picker sync
            $('#popup_button_color_picker').on('change', function() {
                $('#popup_button_color').val($(this).val());
            });
            $('#popup_button_color').on('input', function() {
                $('#popup_button_color_picker').val($(this).val());
            });
            
            // Page targeting toggle
            $('#popup_page_target').on('change', function() {
                if ($(this).val() === 'specific') {
                    $('#popup_target_page_wrapper').show();
                } else {
                    $('#popup_target_page_wrapper').hide();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save Starter Popup Settings
     */
    private function save_starter_popup_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        update_option('alm_starter_popup_enabled', isset($_POST['popup_enabled']) ? '1' : '0');
        update_option('alm_starter_popup_style', sanitize_text_field($_POST['popup_style'] ?? 'modal'));
        update_option('alm_starter_popup_audience', sanitize_text_field($_POST['popup_audience'] ?? 'all'));
        // Handle days before repeat - allow 0 (show immediately) or any positive number
        // Check if the value is explicitly set (even if 0) vs not set at all
        if (isset($_POST['popup_days_before_repeat']) && $_POST['popup_days_before_repeat'] !== '') {
            $days_repeat = absint($_POST['popup_days_before_repeat']);
        } else {
            $days_repeat = 7; // Default if not set
        }
        update_option('alm_starter_popup_days_before_repeat', $days_repeat);
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Starter Popup] Saving days_before_repeat: ' . $days_repeat . ' (POST value: ' . ($_POST['popup_days_before_repeat'] ?? 'not set') . ')');
        }
        update_option('alm_starter_popup_test_mode', isset($_POST['popup_test_mode']) ? '1' : '0');
        update_option('alm_starter_popup_debug_mode', isset($_POST['popup_debug_mode']) ? '1' : '0');
        
        // New fields
        update_option('alm_starter_popup_modal_headline', sanitize_text_field($_POST['popup_modal_headline'] ?? ''));
        update_option('alm_starter_popup_modal_headline_align', sanitize_text_field($_POST['popup_modal_headline_align'] ?? 'center'));
        update_option('alm_starter_popup_modal_content', wp_kses_post($_POST['popup_modal_content'] ?? ''));
        update_option('alm_starter_popup_modal_delay', absint($_POST['popup_modal_delay'] ?? 10));
        update_option('alm_starter_popup_banner_headline', sanitize_text_field($_POST['popup_banner_headline'] ?? ''));
        update_option('alm_starter_popup_banner_content', wp_kses_post($_POST['popup_banner_content'] ?? ''));
        update_option('alm_starter_popup_banner_delay', absint($_POST['popup_banner_delay'] ?? 10));
        update_option('alm_starter_popup_cta_text', sanitize_text_field($_POST['popup_cta_text'] ?? 'Get Started'));
        update_option('alm_starter_popup_button_color', sanitize_hex_color($_POST['popup_button_color'] ?? '#239B90'));
        update_option('alm_starter_popup_button_url', esc_url_raw($_POST['popup_button_url'] ?? '/starter'));
        update_option('alm_starter_popup_modal_animation', sanitize_text_field($_POST['popup_modal_animation'] ?? 'fade'));
        update_option('alm_starter_popup_banner_animation', sanitize_text_field($_POST['popup_banner_animation'] ?? 'slide'));
        update_option('alm_starter_popup_page_target', sanitize_text_field($_POST['popup_page_target'] ?? 'any'));
        
        // Handle multiple target page IDs
        $target_page_ids = array();
        if (isset($_POST['popup_target_page_ids']) && is_array($_POST['popup_target_page_ids'])) {
            $target_page_ids = array_map('absint', $_POST['popup_target_page_ids']);
            $target_page_ids = array_filter($target_page_ids); // Remove zeros
        }
        update_option('alm_starter_popup_target_page_ids', $target_page_ids);
        
        // Handle exclude page IDs
        $exclude_page_ids = array();
        if (isset($_POST['popup_exclude_page_ids']) && is_array($_POST['popup_exclude_page_ids'])) {
            $exclude_page_ids = array_map('absint', $_POST['popup_exclude_page_ids']);
            $exclude_page_ids = array_filter($exclude_page_ids); // Remove zeros
        }
        update_option('alm_starter_popup_exclude_page_ids', $exclude_page_ids);
        
        // Increment cache version to bust WP Engine cache
        $current_cache_version = get_option('alm_starter_popup_cache_version', '1');
        update_option('alm_starter_popup_cache_version', (int)$current_cache_version + 1);
        
        wp_redirect(admin_url('admin.php?page=academy-manager-settings&tab=popup&message=popup_settings_saved'));
        exit;
    }
    
    /**
     * Save free trial lesson IDs
     */
    private function save_free_trial_lessons() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Save free starter lessons
        $free_lesson_ids = isset($_POST['free_trial_lesson_ids']) ? array_map('intval', $_POST['free_trial_lesson_ids']) : array();
        $free_lesson_ids = array_filter($free_lesson_ids); // Remove empty values
        $free_lesson_ids = array_unique($free_lesson_ids); // Remove duplicates
        update_option('alm_free_trial_lesson_ids', $free_lesson_ids);
        
        // Save paid starter lessons
        $paid_lesson_ids = isset($_POST['starter_paid_lesson_ids']) ? array_map('intval', $_POST['starter_paid_lesson_ids']) : array();
        $paid_lesson_ids = array_filter($paid_lesson_ids); // Remove empty values
        $paid_lesson_ids = array_unique($paid_lesson_ids); // Remove duplicates
        update_option('alm_starter_paid_lesson_ids', $paid_lesson_ids);
        
        wp_redirect(admin_url('admin.php?page=academy-manager-settings&tab=free-trial&message=free_trial_saved'));
        exit;
    }
    
    /**
     * Enqueue scripts for free trial lesson picker
     */
    private function enqueue_free_trial_scripts() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Helper function
            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
            
            // FREE STARTER LESSON PICKER
            var freeSearchTimeout;
            var $freeSearchInput = $('#free_trial_lesson_search');
            var $freeResultsDiv = $('#free-trial-lesson-results');
            var $freeLessonsList = $('#free-trial-lessons-ul');
            
            // Show/hide results dropdown
            $freeSearchInput.on('focus', function() {
                if ($(this).val().length >= 2) {
                    $freeResultsDiv.show();
                }
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#free-trial-lesson-picker').length) {
                    $freeResultsDiv.hide();
                }
                if (!$(e.target).closest('#starter-paid-lesson-picker').length) {
                    $('#starter-paid-lesson-results').hide();
                }
            });
            
            // Search for lessons (FREE)
            $freeSearchInput.on('input', function() {
                var query = $(this).val().trim();
                
                clearTimeout(freeSearchTimeout);
                
                if (query.length < 2) {
                    $freeResultsDiv.hide().empty();
                    return;
                }
                
                freeSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'alm_search_posts',
                            nonce: '<?php echo wp_create_nonce('alm_search_posts'); ?>',
                            query: query,
                            search_type: 'lesson'
                        },
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                var html = '<ul style="list-style:none; padding:0; margin:0;">';
                                $.each(response.data, function(i, lesson) {
                                    if (!lesson.is_collection && lesson.ID > 0) {
                                        // Check if already added to free or paid
                                        var alreadyAddedFree = $('input[name="free_trial_lesson_ids[]"][value="' + lesson.ID + '"]').length > 0;
                                        var alreadyAddedPaid = $('input[name="starter_paid_lesson_ids[]"][value="' + lesson.ID + '"]').length > 0;
                                        var alreadyAdded = alreadyAddedFree || alreadyAddedPaid;
                                        var disabledClass = alreadyAdded ? ' style="opacity:0.5; cursor:not-allowed;"' : '';
                                        var disabledText = alreadyAdded ? ' (Already added)' : '';
                                        
                                        html += '<li style="padding:8px; border-bottom:1px solid #eee; cursor:pointer;"' + 
                                                (alreadyAdded ? '' : ' class="add-free-trial-lesson"') + 
                                                ' data-post-id="' + lesson.ID + '"' +
                                                ' data-title="' + escapeHtml(lesson.post_title) + '"' +
                                                ' data-collection="' + escapeHtml(lesson.collection_title || 'No Collection') + '"' +
                                                disabledClass + '>';
                                        html += '<strong>' + escapeHtml(lesson.post_title) + '</strong>';
                                        html += ' <span style="color:#666; font-size:11px;">(' + escapeHtml(lesson.collection_title || 'No Collection') + ')</span>';
                                        html += disabledText;
                                        html += '</li>';
                                    }
                                });
                                html += '</ul>';
                                $freeResultsDiv.html(html).show();
                            } else {
                                $freeResultsDiv.html('<ul style="list-style:none; padding:8px; margin:0;"><li style="color:#666;">No lessons found</li></ul>').show();
                            }
                        },
                        error: function() {
                            $freeResultsDiv.html('<ul style="list-style:none; padding:8px; margin:0;"><li style="color:#a00;">Error searching lessons</li></ul>').show();
                        }
                    });
                }, 300);
            });
            
            // Add lesson to FREE list
            $(document).on('click', '.add-free-trial-lesson', function() {
                var $item = $(this);
                var postId = $item.data('post-id');
                var title = $item.data('title');
                var collection = $item.data('collection');
                
                // Check if already exists
                if ($('input[name="free_trial_lesson_ids[]"][value="' + postId + '"]').length > 0) {
                    return;
                }
                
                // Show the list if hidden
                $freeLessonsList.show().parent().find('p').hide();
                
                // Add to list
                var $li = $('<li style="padding:8px; margin:5px 0; background:#f5f5f5; border-left:3px solid #0073aa;">' +
                    '<span style="font-weight:600;">' + escapeHtml(title) + '</span> ' +
                    '<span style="color:#666; font-size:12px;">(' + escapeHtml(collection) + ')</span> ' +
                    '<input type="hidden" name="free_trial_lesson_ids[]" value="' + postId + '" /> ' +
                    '<button type="button" class="button-link remove-free-trial-lesson" data-post-id="' + postId + '" style="color:#a00; margin-left:10px;">Remove</button>' +
                    '</li>');
                
                $freeLessonsList.append($li);
                
                // Clear search
                $freeSearchInput.val('').focus();
                $freeResultsDiv.hide().empty();
            });
            
            // Remove lesson from FREE list
            $(document).on('click', '.remove-free-trial-lesson', function() {
                $(this).closest('li').remove();
                
                // Hide list if empty
                if ($freeLessonsList.find('li').length === 0) {
                    $freeLessonsList.hide();
                    $freeLessonsList.parent().find('p').show();
                }
            });
            
            // PAID STARTER LESSON PICKER
            var paidSearchTimeout;
            var $paidSearchInput = $('#starter_paid_lesson_search');
            var $paidResultsDiv = $('#starter-paid-lesson-results');
            var $paidLessonsList = $('#starter-paid-lessons-ul');
            
            // Show/hide results dropdown
            $paidSearchInput.on('focus', function() {
                if ($(this).val().length >= 2) {
                    $paidResultsDiv.show();
                }
            });
            
            // Search for lessons (PAID)
            $paidSearchInput.on('input', function() {
                var query = $(this).val().trim();
                
                clearTimeout(paidSearchTimeout);
                
                if (query.length < 2) {
                    $paidResultsDiv.hide().empty();
                    return;
                }
                
                paidSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'alm_search_posts',
                            nonce: '<?php echo wp_create_nonce('alm_search_posts'); ?>',
                            query: query,
                            search_type: 'lesson'
                        },
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                var html = '<ul style="list-style:none; padding:0; margin:0;">';
                                $.each(response.data, function(i, lesson) {
                                    if (!lesson.is_collection && lesson.ID > 0) {
                                        // Check if already added to free or paid
                                        var alreadyAddedFree = $('input[name="free_trial_lesson_ids[]"][value="' + lesson.ID + '"]').length > 0;
                                        var alreadyAddedPaid = $('input[name="starter_paid_lesson_ids[]"][value="' + lesson.ID + '"]').length > 0;
                                        var alreadyAdded = alreadyAddedFree || alreadyAddedPaid;
                                        var disabledClass = alreadyAdded ? ' style="opacity:0.5; cursor:not-allowed;"' : '';
                                        var disabledText = alreadyAdded ? ' (Already added)' : '';
                                        
                                        html += '<li style="padding:8px; border-bottom:1px solid #eee; cursor:pointer;"' + 
                                                (alreadyAdded ? '' : ' class="add-starter-paid-lesson"') + 
                                                ' data-post-id="' + lesson.ID + '"' +
                                                ' data-title="' + escapeHtml(lesson.post_title) + '"' +
                                                ' data-collection="' + escapeHtml(lesson.collection_title || 'No Collection') + '"' +
                                                disabledClass + '>';
                                        html += '<strong>' + escapeHtml(lesson.post_title) + '</strong>';
                                        html += ' <span style="color:#666; font-size:11px;">(' + escapeHtml(lesson.collection_title || 'No Collection') + ')</span>';
                                        html += disabledText;
                                        html += '</li>';
                                    }
                                });
                                html += '</ul>';
                                $paidResultsDiv.html(html).show();
                            } else {
                                $paidResultsDiv.html('<ul style="list-style:none; padding:8px; margin:0;"><li style="color:#666;">No lessons found</li></ul>').show();
                            }
                        },
                        error: function() {
                            $paidResultsDiv.html('<ul style="list-style:none; padding:8px; margin:0;"><li style="color:#a00;">Error searching lessons</li></ul>').show();
                        }
                    });
                }, 300);
            });
            
            // Add lesson to PAID list
            $(document).on('click', '.add-starter-paid-lesson', function() {
                var $item = $(this);
                var postId = $item.data('post-id');
                var title = $item.data('title');
                var collection = $item.data('collection');
                
                // Check if already exists
                if ($('input[name="starter_paid_lesson_ids[]"][value="' + postId + '"]').length > 0) {
                    return;
                }
                
                // Show the list if hidden
                $paidLessonsList.show().parent().find('p').hide();
                
                // Add to list
                var $li = $('<li style="padding:8px; margin:5px 0; background:#f5f5f5; border-left:3px solid #28a745;">' +
                    '<span style="font-weight:600;">' + escapeHtml(title) + '</span> ' +
                    '<span style="color:#666; font-size:12px;">(' + escapeHtml(collection) + ')</span> ' +
                    '<input type="hidden" name="starter_paid_lesson_ids[]" value="' + postId + '" /> ' +
                    '<button type="button" class="button-link remove-starter-paid-lesson" data-post-id="' + postId + '" style="color:#a00; margin-left:10px;">Remove</button>' +
                    '</li>');
                
                $paidLessonsList.append($li);
                
                // Clear search
                $paidSearchInput.val('').focus();
                $paidResultsDiv.hide().empty();
            });
            
            // Remove lesson from PAID list
            $(document).on('click', '.remove-starter-paid-lesson', function() {
                $(this).closest('li').remove();
                
                // Hide list if empty
                if ($paidLessonsList.find('li').length === 0) {
                    $paidLessonsList.hide();
                    $paidLessonsList.parent().find('p').show();
                }
            });
        });
        </script>
        <style>
        #free-trial-lesson-picker, #starter-paid-lesson-picker {
            position: relative;
        }
        #free-trial-lesson-results ul li:hover, #starter-paid-lesson-results ul li:hover {
            background: #f0f0f0;
        }
        #free-trial-lessons-ul li:hover, #starter-paid-lessons-ul li:hover {
            background: #e8e8e8 !important;
        }
        </style>
        <?php
    }
}
