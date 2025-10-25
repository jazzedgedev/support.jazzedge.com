<?php
/**
 * Plugin Name: Academy Lesson Manager
 * Plugin URI: https://jazzedge.com
 * Description: Manage Academy courses, lessons, and chapters with CRUD capabilities
 * Version: 1.0.0
 * Author: JazzEdge
 * License: GPL v2 or later
 * Text Domain: academy-lesson-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ALM_VERSION', '1.0.0');
define('ALM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALM_PLUGIN_FILE', __FILE__);

/**
 * Main Academy Lesson Manager Class
 */
class Academy_Lesson_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Debug: Log that constructor is called
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ALM: Academy_Lesson_Manager constructor called');
        }
        
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Debug: Log that init is called
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ALM: Plugin init() method called');
        }
        
        // Load plugin text domain
        load_plugin_textdomain('academy-lesson-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
        
        
        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        require_once ALM_PLUGIN_DIR . 'includes/class-database.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-helpers.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-collections.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-courses.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-lessons.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-chapters.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-settings.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-post-sync.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-bunny-api.php';
    }
    
    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add AJAX handler for chapter reordering
        add_action('wp_ajax_alm_update_chapter_order', array($this, 'ajax_update_chapter_order'));
        
        // Add AJAX handler for Bunny.net metadata fetching
        add_action('wp_ajax_alm_fetch_bunny_metadata', array($this, 'ajax_fetch_bunny_metadata'));
        
        // Add AJAX handler for testing Bunny.net connection
        add_action('wp_ajax_alm_test_bunny_connection', array($this, 'ajax_test_bunny_connection'));
        
        // Add AJAX handler for debugging Bunny.net config
        add_action('wp_ajax_alm_debug_bunny_config', array($this, 'ajax_debug_bunny_config'));
        
        // Add AJAX handlers for resources
        add_action('wp_ajax_alm_add_resource', array($this, 'ajax_add_resource'));
        add_action('wp_ajax_alm_delete_resource', array($this, 'ajax_delete_resource'));
        
        // Add WordPress hooks for reverse sync
        add_action('save_post', array($this, 'handle_post_save'));
        add_action('delete_post', array($this, 'handle_post_delete'));
        
        // Initialize admin classes
        new ALM_Admin_Collections();
        new ALM_Admin_Courses();
        new ALM_Admin_Lessons();
        new ALM_Admin_Chapters();
        new ALM_Admin_Settings();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Lesson Manager', 'academy-lesson-manager'),
            __('Lesson Manager', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager',
            array($this, 'admin_page_collections'),
            'dashicons-book-alt',
            30
        );
        
        // Submenus
        add_submenu_page(
            'academy-manager',
            __('Collections', 'academy-lesson-manager'),
            __('Collections', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager',
            array($this, 'admin_page_collections')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Lessons', 'academy-lesson-manager'),
            __('Lessons', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-lessons',
            array($this, 'admin_page_lessons')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Chapters', 'academy-lesson-manager'),
            __('Chapters', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-chapters',
            array($this, 'admin_page_chapters')
        );
        
        add_submenu_page(
            'academy-manager',
            __('Settings', 'academy-lesson-manager'),
            __('Settings', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    /**
     * Admin page callbacks
     */
    public function admin_page_collections() {
        $admin_collections = new ALM_Admin_Collections();
        $admin_collections->render_page();
    }
    
    public function admin_page_courses() {
        $admin_courses = new ALM_Admin_Courses();
        $admin_courses->render_page();
    }
    
    public function admin_page_lessons() {
        $admin_lessons = new ALM_Admin_Lessons();
        $admin_lessons->render_page();
    }
    
    public function admin_page_chapters() {
        $admin_chapters = new ALM_Admin_Chapters();
        $admin_chapters->render_page();
    }
    
    public function admin_page_settings() {
        $admin_settings = new ALM_Admin_Settings();
        $admin_settings->render_settings_page();
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'academy-manager') === false) {
            return;
        }
        
        wp_enqueue_style(
            'alm-admin-css',
            ALM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ALM_VERSION
        );
        
        wp_enqueue_script(
            'alm-admin-js',
            ALM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            ALM_VERSION,
            true
        );
        
        // Enqueue media uploader scripts
        wp_enqueue_media();
        
        // Localize script with AJAX data
        wp_localize_script('alm-admin-js', 'alm_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alm_admin_nonce')
        ));
        
        // Add JavaScript for duration formatting and chapter reordering
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                function formatDuration(seconds) {
                    if (!seconds || seconds <= 0) return "00:00:00";
                    
                    var hours = Math.floor(seconds / 3600);
                    var minutes = Math.floor((seconds % 3600) / 60);
                    var secs = seconds % 60;
                    
                    return String(hours).padStart(2, "0") + ":" + 
                           String(minutes).padStart(2, "0") + ":" + 
                           String(secs).padStart(2, "0");
                }
                
                // Update duration display when input changes
                $("#duration").on("input", function() {
                    var seconds = parseInt($(this).val()) || 0;
                    var formatted = formatDuration(seconds);
                    $(this).next(".description").text("(" + formatted + ")");
                });
                
                // Chapter reordering functionality
                if ($(".alm-chapter-reorder").length > 0) {
                    $(".alm-chapter-reorder tbody").sortable({
                        handle: ".chapter-drag-handle",
                        placeholder: "chapter-placeholder",
                        update: function(event, ui) {
                            var chapterIds = [];
                            $(this).find("tr").each(function(index) {
                                var chapterId = $(this).data("chapter-id");
                                if (chapterId) {
                                    chapterIds.push(chapterId);
                                }
                            });
                            
                            // Update order numbers in the display
                            $(this).find("tr").each(function(index) {
                                $(this).find(".chapter-order").text(index + 1);
                            });
                            
                            // Send AJAX request to update database
                            $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    action: "alm_update_chapter_order",
                                    chapter_ids: chapterIds,
                                    nonce: alm_admin.nonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Show success message
                                        $("<div class=\"notice notice-success is-dismissible\"><p>Chapter order updated successfully.</p></div>")
                                            .insertAfter(".alm-chapter-reorder")
                                            .delay(3000)
                                            .fadeOut();
                                    } else {
                                        console.error("AJAX Error:", response);
                                        alert("Error updating chapter order: " + (response.data || "Unknown error"));
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Request Failed:", xhr, status, error);
                                    alert("Error updating chapter order. Please refresh the page.");
                                }
                            });
                        }
                    });
                }
            });
        ');
        
        // Add JavaScript for Bunny.net metadata fetching
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                // Bunny.net metadata fetching
                $(".fetch-bunny-metadata").on("click", function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var $urlField = $button.siblings("input[name=\'bunny_url\']");
                    var $durationField = $("input[name=\'duration\']");
                    var bunnyUrl = $urlField.val();
                    
                    if (!bunnyUrl) {
                        alert("Please enter a Bunny URL first.");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Fetching...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_fetch_bunny_metadata",
                            bunny_url: bunnyUrl,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;
                                var updates = [];
                                
                                // Update duration field
                                if (data.duration > 0) {
                                    $durationField.val(data.duration);
                                    
                                    // Update duration display
                                    var hours = Math.floor(data.duration / 3600);
                                    var minutes = Math.floor((data.duration % 3600) / 60);
                                    var seconds = data.duration % 60;
                                    var formatted = String(hours).padStart(2, "0") + ":" + 
                                                   String(minutes).padStart(2, "0") + ":" + 
                                                   String(seconds).padStart(2, "0");
                                    $durationField.next(".description").text("(" + formatted + ")");
                                    updates.push("Duration: " + Math.floor(data.duration / 60) + " minutes");
                                }
                                
                                // Update release date field
                                if (data.created_at && data.created_at !== "0000-00-00" && data.created_at !== "0000-00-00T00:00:00Z") {
                                    var $releaseDateField = $("input[name=\'post_date\']");
                                    console.log("Release date field found:", $releaseDateField.length);
                                    console.log("Bunny.net created_at:", data.created_at);
                                    
                                    if ($releaseDateField.length > 0) {
                                        // Convert Bunny.net date to YYYY-MM-DD format
                                        var date = new Date(data.created_at);
                                        
                                        // Check if date is valid
                                        if (!isNaN(date.getTime())) {
                                            var formattedDate = date.getFullYear() + "-" + 
                                                               String(date.getMonth() + 1).padStart(2, "0") + "-" + 
                                                               String(date.getDate()).padStart(2, "0");
                                            console.log("Formatted date:", formattedDate);
                                            $releaseDateField.val(formattedDate);
                                            updates.push("Release Date: " + formattedDate);
                                        } else {
                                            console.log("Invalid date from Bunny.net:", data.created_at);
                                        }
                                    } else {
                                        console.log("Release date field not found!");
                                    }
                                } else {
                                    console.log("No valid created_at date from Bunny.net:", data.created_at);
                                }
                                
                                // Show success message with all updates
                                var message = "Video metadata fetched successfully! " + updates.join(", ");
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("tr"))
                                    .delay(3000)
                                    .fadeOut();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error fetching video metadata. Please check your Bunny.net API configuration.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Fetch Metadata");
                        }
                    });
                });
            });
        ');
        
        // Add JavaScript for resource management
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                // Toggle add resource form
                $("#alm-add-resource-btn").on("click", function() {
                    $("#alm-add-resource-form").slideToggle();
                });
                
                // Cancel add resource form
                $("#alm-cancel-resource-btn").on("click", function() {
                    $("#alm-add-resource-form").slideUp();
                    $("#alm-resource-type").val("");
                    $("#alm-resource-url").val("");
                    $("#alm-resource-attachment-id").val("");
                    $("#alm-clear-media-btn").hide();
                });
                
                // Media library selector
                var mediaUploader;
                $("#alm-select-media-btn").on("click", function(e) {
                    e.preventDefault();
                    
                    // If the uploader object has already been created, reopen it
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    
                    // Create the media uploader
                    mediaUploader = wp.media({
                        title: "Select Resource File",
                        button: {
                            text: "Use this file"
                        },
                        multiple: false,
                        library: {
                            type: ["application/pdf", "audio", "application/zip", "application/x-zip-compressed"]
                        }
                    });
                    
                    // When a file is selected, run a callback
                    mediaUploader.on("select", function() {
                        var attachment = mediaUploader.state().get("selection").first().toJSON();
                        $("#alm-resource-attachment-id").val(attachment.id);
                        $("#alm-resource-url").val(attachment.url);
                        $("#alm-clear-media-btn").show();
                    });
                    
                    // Open the uploader
                    mediaUploader.open();
                });
                
                // Clear selected media
                $("#alm-clear-media-btn").on("click", function() {
                    $("#alm-resource-attachment-id").val("");
                    $("#alm-resource-url").val("");
                    $(this).hide();
                });
                
                // Allow manual URL entry (double-click to enable)
                $("#alm-resource-url").on("dblclick", function() {
                    $(this).prop("readonly", false).css("background-color", "#fff");
                });
                
                // Add resource
                $("#alm-save-resource-btn").on("click", function() {
                    var resourceType = $("#alm-resource-type").val();
                    var resourceUrl = $("#alm-resource-url").val();
                    var attachmentId = $("#alm-resource-attachment-id").val();
                    var lessonId = ' . (isset($_GET['id']) ? intval($_GET['id']) : 0) . ';
                    
                    if (!resourceType || !resourceUrl) {
                        alert("Please select a resource type and choose a file.");
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop("disabled", true).text("Adding...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_add_resource",
                            lesson_id: lessonId,
                            resource_type: resourceType,
                            resource_url: resourceUrl,
                            attachment_id: attachmentId,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page to show updated resources
                                location.reload();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error adding resource. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Add Resource");
                        }
                    });
                });
                
                // Delete resource
                $(document).on("click", ".alm-delete-resource", function(e) {
                    e.preventDefault();
                    
                    if (!confirm("Are you sure you want to delete this resource?")) {
                        return;
                    }
                    
                    var $link = $(this);
                    var resourceType = $link.data("type");
                    var lessonId = $link.data("lesson-id");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_delete_resource",
                            lesson_id: lessonId,
                            resource_type: resourceType,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page to show updated resources
                                location.reload();
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error deleting resource. Please try again.");
                        }
                    });
                });
            });
        ');
        
            // Add CSS for drag and drop
            wp_add_inline_style('alm-admin-css', '
                .chapter-drag-handle {
                    cursor: move !important;
                    color: #666;
                    font-weight: bold;
                    user-select: none;
                }
                .chapter-drag-handle:hover {
                    color: #0073aa;
                }
                .chapter-placeholder {
                    background-color: #f0f0f1 !important;
                    border: 2px dashed #c3c4c7 !important;
                    height: 40px;
                }
                .ui-sortable-helper {
                    background-color: #fff !important;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
                }
                
                /* Icon button styling */
                .column-actions .button {
                    min-width: 32px;
                    height: 32px;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .column-actions .dashicons {
                    font-size: 16px;
                    width: 16px;
                    height: 16px;
                    line-height: 1;
                }
                .column-actions .button:hover .dashicons {
                    color: #0073aa;
                }
            ');
    }
    
    /**
     * AJAX handler for updating chapter order
     */
    public function ajax_update_chapter_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $chapter_ids = $_POST['chapter_ids'];
        
        if (!is_array($chapter_ids)) {
            wp_send_json_error('Invalid chapter IDs');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('chapters');
        
        // Update each chapter's menu_order
        foreach ($chapter_ids as $order => $chapter_id) {
            $wpdb->update(
                $table_name,
                array('menu_order' => $order + 1),
                array('ID' => intval($chapter_id)),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success('Chapter order updated');
    }
    
    /**
     * AJAX handler for fetching Bunny.net metadata
     */
    public function ajax_fetch_bunny_metadata() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bunny_url = sanitize_text_field($_POST['bunny_url']);
        
        if (empty($bunny_url)) {
            wp_send_json_error('Bunny URL is required');
        }
        
        $bunny_api = new ALM_Bunny_API();
        
        if (!$bunny_api->is_configured()) {
            wp_send_json_error('Bunny.net API not configured. Please set Library ID and API Key in settings.');
        }
        
        $video_info = $bunny_api->get_video_info($bunny_url);
        
        if (!$video_info) {
            wp_send_json_error('Could not fetch video metadata. Please check the URL and API configuration.');
        }
        
        wp_send_json_success($video_info);
    }
    
    /**
     * AJAX handler for testing Bunny.net connection
     */
    public function ajax_test_bunny_connection() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bunny_api = new ALM_Bunny_API();
        $result = $bunny_api->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX handler for debugging Bunny.net configuration
     */
    public function ajax_debug_bunny_config() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bunny_api = new ALM_Bunny_API();
        $debug_info = $bunny_api->debug_request();
        
        wp_send_json_success($debug_info);
    }
    
    /**
     * AJAX handler for adding a resource
     */
    public function ajax_add_resource() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        $resource_type = sanitize_text_field($_POST['resource_type']);
        $resource_url = sanitize_text_field($_POST['resource_url']);
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        
        if (empty($lesson_id) || empty($resource_type) || empty($resource_url)) {
            wp_send_json_error('All fields are required');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        // Get current resources
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT resources FROM {$table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error('Lesson not found');
        }
        
        // Unserialize resources
        $resources = maybe_unserialize($lesson->resources);
        if (!is_array($resources)) {
            $resources = array();
        }
        
        // Store both URL and attachment ID if provided
        if ($attachment_id > 0) {
            $resources[$resource_type] = array(
                'url' => $resource_url,
                'attachment_id' => $attachment_id
            );
        } else {
            $resources[$resource_type] = $resource_url;
        }
        
        // Update database
        $result = $wpdb->update(
            $table_name,
            array('resources' => serialize($resources)),
            array('ID' => $lesson_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Resource added successfully',
                'resources' => ALM_Helpers::format_serialized_resources(serialize($resources))
            ));
        } else {
            wp_send_json_error('Failed to add resource');
        }
    }
    
    /**
     * AJAX handler for deleting a resource
     */
    public function ajax_delete_resource() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        $resource_type = sanitize_text_field($_POST['resource_type']);
        
        if (empty($lesson_id) || empty($resource_type)) {
            wp_send_json_error('Invalid parameters');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        // Get current resources
        $lesson = $wpdb->get_row($wpdb->prepare(
            "SELECT resources FROM {$table_name} WHERE ID = %d",
            $lesson_id
        ));
        
        if (!$lesson) {
            wp_send_json_error('Lesson not found');
        }
        
        // Unserialize resources
        $resources = maybe_unserialize($lesson->resources);
        if (!is_array($resources)) {
            $resources = array();
        }
        
        // Remove resource
        unset($resources[$resource_type]);
        
        // Update database
        $result = $wpdb->update(
            $table_name,
            array('resources' => serialize($resources)),
            array('ID' => $lesson_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Resource deleted successfully',
                'resources' => ALM_Helpers::format_serialized_resources(serialize($resources))
            ));
        } else {
            wp_send_json_error('Failed to delete resource');
        }
    }
    
    /**
     * Handle WordPress post save (reverse sync)
     */
    public function handle_post_save($post_id) {
        // Skip autosaves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        $sync = new ALM_Post_Sync();
        
        // Sync based on post type
        if ($post->post_type === 'lesson') {
            $sync->sync_post_to_lesson($post_id);
        } elseif ($post->post_type === 'lesson-collection') {
            $sync->sync_post_to_collection($post_id);
        }
    }
    
    /**
     * Handle WordPress post deletion (reverse sync)
     */
    public function handle_post_delete($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        // Only handle our post types
        if (!in_array($post->post_type, array('lesson', 'lesson-collection'))) {
            return;
        }
        
        // Get ALM ID from ACF
        $alm_id = get_field('alm_lesson_id', $post_id) ?: get_field('alm_collection_id', $post_id);
        
        if (!$alm_id) {
            return;
        }
        
        // Delete from ALM table
        global $wpdb;
        $database = new ALM_Database();
        
        if ($post->post_type === 'lesson') {
            $table_name = $database->get_table_name('lessons');
        } else {
            $table_name = $database->get_table_name('collections');
        }
        
        $wpdb->delete($table_name, array('ID' => $alm_id));
    }
    
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Include required files first
        $this->include_files();
        
        // Create database tables
        $database = new ALM_Database();
        $database->create_tables();
        
        // Set activation flag
        update_option('alm_plugin_activated', true);
        update_option('alm_version', ALM_VERSION);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
        delete_option('alm_plugin_activated');
    }
}

// Initialize the plugin
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('ALM: About to instantiate Academy_Lesson_Manager');
}
new Academy_Lesson_Manager();
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('ALM: Academy_Lesson_Manager instantiated');
}
