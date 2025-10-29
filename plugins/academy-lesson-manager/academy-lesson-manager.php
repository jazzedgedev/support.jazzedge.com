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
        // Register AJAX handlers immediately - these need to be available for both admin and frontend
        // Register on both constructor and init to ensure they're available
        add_action('init', array($this, 'register_ajax_handlers'), 5);
        $this->register_ajax_handlers();
        
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Register AJAX handlers for frontend actions
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_alm_toggle_resource_favorite', array($this, 'ajax_toggle_resource_favorite'));
        add_action('wp_ajax_alm_update_favorites_order', array($this, 'ajax_update_favorites_order'));
        add_action('wp_ajax_alm_delete_favorite', array($this, 'ajax_delete_favorite'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
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
        require_once ALM_PLUGIN_DIR . 'includes/class-admin-event-migration.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-post-sync.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-bunny-api.php';
        require_once ALM_PLUGIN_DIR . 'includes/class-vimeo-api.php';
    }
    
    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add AJAX handler for chapter reordering
        add_action('wp_ajax_alm_update_chapter_order', array($this, 'ajax_update_chapter_order'));
        
        // Add AJAX handler for lesson reordering in collections
        add_action('wp_ajax_alm_update_lesson_order', array($this, 'ajax_update_lesson_order'));
        
        // Add AJAX handler for Bunny.net metadata fetching
        add_action('wp_ajax_alm_fetch_bunny_metadata', array($this, 'ajax_fetch_bunny_metadata'));
        
        // Add AJAX handler for testing Bunny.net connection
        add_action('wp_ajax_alm_test_bunny_connection', array($this, 'ajax_test_bunny_connection'));
        
        // Add AJAX handler for debugging Bunny.net config
        add_action('wp_ajax_alm_debug_bunny_config', array($this, 'ajax_debug_bunny_config'));
        
        // Add AJAX handlers for resources
        add_action('wp_ajax_alm_add_resource', array($this, 'ajax_add_resource'));
        add_action('wp_ajax_alm_delete_resource', array($this, 'ajax_delete_resource'));
        
        // Add AJAX handler for calculating lesson duration from chapters
        add_action('wp_ajax_alm_calculate_lesson_duration', array($this, 'ajax_calculate_lesson_duration'));
        
        // Add AJAX handler for calculating all chapter durations from Bunny API
        add_action('wp_ajax_alm_calculate_all_bunny_durations', array($this, 'ajax_calculate_all_bunny_durations'));
        
        // Add AJAX handler for calculating all chapter durations from Vimeo API
        add_action('wp_ajax_alm_calculate_all_vimeo_durations', array($this, 'ajax_calculate_all_vimeo_durations'));
        
        // Note: Frontend AJAX handlers (favorites management) are registered in constructor
        
        // Add WordPress hooks for reverse sync
        add_action('save_post', array($this, 'handle_post_save'));
        add_action('delete_post', array($this, 'handle_post_delete'));
        
        // Initialize admin classes
        new ALM_Admin_Collections();
        new ALM_Admin_Courses();
        new ALM_Admin_Lessons();
        new ALM_Admin_Chapters();
        new ALM_Admin_Settings();
        new ALM_Admin_Event_Migration();
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
            __('Event Migration', 'academy-lesson-manager'),
            __('Event Migration', 'academy-lesson-manager'),
            'manage_options',
            'academy-manager-event-migration',
            array($this, 'admin_page_event_migration')
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
    
    public function admin_page_event_migration() {
        $admin_event_migration = new ALM_Admin_Event_Migration();
        $admin_event_migration->render_page();
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
                
                // Lesson reordering functionality in collections
                if ($(".alm-collection-lessons").length > 0) {
                    $(".alm-collection-lessons tbody.ui-sortable").sortable({
                        handle: ".dashicons-menu",
                        cursor: "move",
                        placeholder: "sortable-placeholder",
                        tolerance: "pointer",
                        opacity: 0.6,
                        update: function(event, ui) {
                            var lessonIds = [];
                            $(this).find("tr").each(function(index) {
                                var lessonId = $(this).data("lesson-id");
                                if (lessonId) {
                                    lessonIds.push(lessonId);
                                }
                            });
                            
                            // Send AJAX request to update database
                            $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    action: "alm_update_lesson_order",
                                    lesson_ids: lessonIds,
                                    nonce: alm_admin.nonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Show success message
                                        $("<div class=\"notice notice-success is-dismissible\"><p>Lesson order updated successfully.</p></div>")
                                            .insertAfter(".alm-collection-lessons h3")
                                            .delay(3000)
                                            .fadeOut();
                                    } else {
                                        console.error("AJAX Error:", response);
                                        alert("Error updating lesson order: " + (response.data || "Unknown error"));
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Request Failed:", xhr, status, error);
                                    alert("Error updating lesson order. Please refresh the page.");
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
        
        // Add JavaScript for calculating all Bunny durations
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                $("#alm-calculate-bunny-durations").on("click", function() {
                    var $button = $(this);
                    var lessonId = $button.data("lesson-id");
                    
                    if (!lessonId) {
                        alert("Error: No lesson ID found");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_calculate_all_bunny_durations",
                            nonce: alm_admin.nonce,
                            lesson_id: lessonId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Successfully updated " + response.data.updated + " chapters. ";
                                if (response.data.total_duration > 0) {
                                    var hours = Math.floor(response.data.total_duration / 3600);
                                    var minutes = Math.floor((response.data.total_duration % 3600) / 60);
                                    message += "Total lesson duration: " + (hours > 0 ? hours + "h " : "") + minutes + "m";
                                } else {
                                    message += "Total lesson duration: 0";
                                }
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(5000)
                                    .fadeOut();
                                
                                // Reload page after a short delay
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error calculating durations. Please check your Bunny.net API configuration.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Calculate All Bunny Durations");
                        }
                    });
                });
            });
        ');
        
        // Add JavaScript for calculating all Vimeo durations
        wp_add_inline_script('alm-admin-js', '
            jQuery(document).ready(function($) {
                $("#alm-calculate-vimeo-durations").on("click", function() {
                    var $button = $(this);
                    var lessonId = $button.data("lesson-id");
                    
                    if (!lessonId) {
                        alert("Error: No lesson ID found");
                        return;
                    }
                    
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: alm_admin.ajax_url,
                        type: "POST",
                        data: {
                            action: "alm_calculate_all_vimeo_durations",
                            nonce: alm_admin.nonce,
                            lesson_id: lessonId
                        },
                        success: function(response) {
                            if (response.success) {
                                var message = "Successfully updated " + response.data.updated + " chapters. ";
                                if (response.data.total_duration > 0) {
                                    var hours = Math.floor(response.data.total_duration / 3600);
                                    var minutes = Math.floor((response.data.total_duration % 3600) / 60);
                                    message += "Total lesson duration: " + (hours > 0 ? hours + "h " : "") + minutes + "m";
                                } else {
                                    message += "Total lesson duration: 0";
                                }
                                $("<div class=\"notice notice-success is-dismissible\"><p>" + message + "</p></div>")
                                    .insertAfter($button.closest("h3"))
                                    .delay(2000)
                                    .fadeOut();
                                
                                // Reload page to show updated durations
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                alert("Error: " + (response.data ? response.data : "Unknown error"));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error calculating Vimeo durations. Please check the console for details.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Calculate All Vimeo Durations");
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
                
                // Show/hide fields based on resource type
                function toggleResourceFields() {
                    var resourceType = $("#alm-resource-type").val();
                    if (resourceType === "note") {
                        $(".alm-resource-file-row").hide();
                        $(".alm-resource-note-row").show();
                    } else {
                        $(".alm-resource-file-row").show();
                        $(".alm-resource-note-row").hide();
                    }
                }
                
                // Trigger on load and when type changes
                toggleResourceFields();
                $("#alm-resource-type").on("change", toggleResourceFields);
                
                // Cancel add resource form
                $("#alm-cancel-resource-btn").on("click", function() {
                    $("#alm-add-resource-form").slideUp();
                    $("#alm-resource-type").val("").prop("disabled", false);
                    $("#alm-resource-url").val("");
                    $("#alm-resource-attachment-id").val("");
                    $("#alm-resource-label").val("");
                    $("#alm-resource-note").val("");
                    $("#alm-edit-resource-type").val("");
                    $("#alm-clear-media-btn").hide();
                    $(".alm-resource-file-row").show();
                    $(".alm-resource-note-row").hide();
                    $("#alm-add-resource-form h4").text("Add New Resource");
                    $("#alm-save-resource-btn").text("Add Resource");
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
                        multiple: false
                        // Note: No type filter allows all file types including HTML
                    });
                    
                    // When a file is selected, run a callback
                    mediaUploader.on("select", function() {
                        var attachment = mediaUploader.state().get("selection").first().toJSON();
                        $("#alm-resource-attachment-id").val(attachment.id);
                        $("#alm-resource-url").val(attachment.url);
                        $("#alm-clear-media-btn").show();
                        
                        // Auto-detect file type based on extension
                        var fileName = attachment.filename || attachment.url || "";
                        var fileExtension = fileName.toLowerCase().split(".").pop();
                        
                        // Auto-select resource type based on file extension
                        if (fileExtension === "html") {
                            $("#alm-resource-type").val("ireal");
                        } else if (fileExtension === "mp3") {
                            $("#alm-resource-type").val("jam");
                        } else if (fileExtension === "zip") {
                            $("#alm-resource-type").val("zip");
                        }
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
                
                // Auto-detect resource type when manually entering URL
                $("#alm-resource-url").on("input paste", function() {
                    var url = $(this).val();
                    if (url) {
                        var fileExtension = url.toLowerCase().split(".").pop().split("?")[0]; // Remove query params
                        
                        // Only auto-select if resource type is not already chosen or is disabled (edit mode)
                        if (!$("#alm-resource-type").prop("disabled")) {
                            if (fileExtension === "html") {
                                $("#alm-resource-type").val("ireal");
                            } else if (fileExtension === "mp3") {
                                $("#alm-resource-type").val("jam");
                            } else if (fileExtension === "zip") {
                                $("#alm-resource-type").val("zip");
                            }
                        }
                    }
                });
                
                // Add/Edit resource
                $("#alm-save-resource-btn").on("click", function() {
                    var resourceType = $("#alm-resource-type").val();
                    var resourceUrl = $("#alm-resource-url").val();
                    var attachmentId = $("#alm-resource-attachment-id").val();
                    var resourceLabel = $("#alm-resource-label").val();
                    var resourceNote = $("#alm-resource-note").val();
                    var editResourceType = $("#alm-edit-resource-type").val();
                    var lessonId = ' . (isset($_GET['id']) ? intval($_GET['id']) : 0) . ';
                    var isEdit = editResourceType !== "";
                    
                    // Use editResourceType if editing and resourceType is empty (disabled field)
                    if (isEdit && !resourceType) {
                        resourceType = editResourceType;
                    }
                    
                    // For note type, use note content as URL
                    if (resourceType === "note" && resourceNote) {
                        resourceUrl = resourceNote;
                    }
                    
                    if (!resourceType || (!resourceUrl && resourceType !== "note")) {
                        alert("Please select a resource type and choose a file or enter note content.");
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop("disabled", true).text(isEdit ? "Updating..." : "Adding...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_add_resource",
                            lesson_id: lessonId,
                            resource_type: resourceType,
                            resource_url: resourceUrl,
                            attachment_id: attachmentId,
                            resource_label: resourceLabel,
                            old_resource_type: editResourceType,
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
                            alert("Error " + (isEdit ? "updating" : "adding") + " resource. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text(isEdit ? "Update Resource" : "Add Resource");
                        }
                    });
                });
                
                // Edit resource
                $(document).on("click", ".alm-edit-resource", function(e) {
                    e.preventDefault();
                    
                    var $link = $(this);
                    var resourceType = $link.data("type");
                    var resourceUrl = $link.data("url");
                    var attachmentId = $link.data("attachment-id");
                    var resourceLabel = $link.data("label") || "";
                    
                    // Populate form with existing data
                    $("#alm-edit-resource-type").val(resourceType);
                    $("#alm-resource-type").val(resourceType).prop("disabled", true);
                    $("#alm-resource-url").val(resourceUrl);
                    $("#alm-resource-attachment-id").val(attachmentId);
                    $("#alm-resource-label").val(resourceLabel);
                    
                    // Handle note type - if its a note, populate note field with url content
                    if (resourceType === "note") {
                        $("#alm-resource-note").val(resourceUrl);
                        $(".alm-resource-file-row").hide();
                        $(".alm-resource-note-row").show();
                    } else {
                        $(".alm-resource-file-row").show();
                        $(".alm-resource-note-row").hide();
                    }
                    
                    if (attachmentId > 0) {
                        $("#alm-clear-media-btn").show();
                    }
                    
                    // Change button text and form title
                    $("#alm-add-resource-form h4").text("Edit Resource");
                    $("#alm-save-resource-btn").text("Update Resource");
                    
                    // Show form
                    $("#alm-add-resource-form").slideDown();
                    
                    // Scroll to form
                    $("html, body").animate({
                        scrollTop: $("#alm-add-resource-form").offset().top - 50
                    }, 500);
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
                
                // Update duration from chapters
                $("#alm-update-duration-btn").on("click", function() {
                    var lessonId = ' . (isset($_GET['id']) ? intval($_GET['id']) : 0) . ';
                    
                    if (!lessonId) {
                        alert("Error: Lesson ID not found.");
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop("disabled", true).text("Calculating...");
                    
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "alm_calculate_lesson_duration",
                            lesson_id: lessonId,
                            nonce: alm_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update the duration field
                                $("#duration").val(response.data.duration);
                                // Update the displayed duration
                                $("#duration").next(".description").text("(" + response.data.formatted + ")");
                                alert("Duration updated successfully to " + response.data.formatted + "!");
                            } else {
                                alert("Error: " + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr, status, error);
                            alert("Error calculating duration. Please try again.");
                        },
                        complete: function() {
                            $button.prop("disabled", false).text("Update from Chapters");
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
     * AJAX handler for updating lesson order in collections
     */
    public function ajax_update_lesson_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_ids = $_POST['lesson_ids'];
        
        if (!is_array($lesson_ids)) {
            wp_send_json_error('Invalid lesson IDs');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $table_name = $database->get_table_name('lessons');
        
        // Ensure menu_order column exists
        $database->check_and_add_menu_order_column();
        
        // Update each lesson's menu_order
        foreach ($lesson_ids as $order => $lesson_id) {
            $wpdb->update(
                $table_name,
                array('menu_order' => $order), // Use 0-based order (0, 1, 2, etc.)
                array('ID' => intval($lesson_id)),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success('Lesson order updated');
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
        $old_resource_type = isset($_POST['old_resource_type']) ? sanitize_text_field($_POST['old_resource_type']) : '';
        
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
        
        // If editing, remove the old resource first
        if (!empty($old_resource_type) && isset($resources[$old_resource_type])) {
            unset($resources[$old_resource_type]);
        }
        
        // For certain resource types, support multiple entries (jam, ireal, etc.)
        $multi_resource_types = array('jam', 'ireal', 'sheet_music', 'zip');
        
        if (in_array($resource_type, $multi_resource_types)) {
            // Find all existing resources of this type to determine the count
            $count = 1;
            foreach ($resources as $key => $value) {
                if (strpos($key, $resource_type) === 0) {
                    $count++;
                }
            }
            
            // If there's already one of this type, append the count
            $resource_key = ($count > 1) ? $resource_type . $count : $resource_type;
        } else {
            $resource_key = $resource_type;
        }
        
        // Get resource label if provided
        $resource_label = isset($_POST['resource_label']) ? sanitize_text_field(substr($_POST['resource_label'], 0, 30)) : '';
        
        // Store both URL and attachment ID if provided
        if ($attachment_id > 0) {
            $resources[$resource_key] = array(
                'url' => $resource_url,
                'attachment_id' => $attachment_id,
                'label' => $resource_label
            );
        } else {
            $resources[$resource_key] = array(
                'url' => $resource_url,
                'label' => $resource_label
            );
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
     * AJAX handler for calculating lesson duration from chapters
     */
    public function ajax_calculate_lesson_duration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($lesson_id)) {
            wp_send_json_error('Invalid lesson ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        
        // Get all chapters for this lesson
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT duration FROM {$chapters_table} WHERE lesson_id = %d",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error('No chapters found for this lesson');
        }
        
        // Calculate total duration
        $total_duration = 0;
        foreach ($chapters as $chapter) {
            $total_duration += intval($chapter->duration);
        }
        
        // Format the duration
        $formatted_duration = ALM_Helpers::format_duration($total_duration);
        
        wp_send_json_success(array(
            'duration' => $total_duration,
            'formatted' => $formatted_duration,
            'chapters_count' => count($chapters)
        ));
    }
    
    /**
     * AJAX handler for calculating all chapter durations from Bunny API
     */
    public function ajax_calculate_all_bunny_durations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($lesson_id)) {
            wp_send_json_error('Invalid lesson ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        $lessons_table = $database->get_table_name('lessons');
        
        // Get all chapters for this lesson that have Bunny URLs
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, bunny_url FROM {$chapters_table} WHERE lesson_id = %d AND bunny_url != '' AND bunny_url IS NOT NULL",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error('No chapters with Bunny URLs found for this lesson');
        }
        
        $bunny_api = new ALM_Bunny_API();
        
        if (!$bunny_api->is_configured()) {
            wp_send_json_error('Bunny.net API not configured. Please set Library ID and API Key in settings.');
        }
        
        $updated_count = 0;
        $total_duration = 0;
        
        foreach ($chapters as $chapter) {
            $duration = $bunny_api->get_video_duration($chapter->bunny_url);
            
            if ($duration !== false && $duration > 0) {
                // Update the chapter duration
                $wpdb->update(
                    $chapters_table,
                    array('duration' => $duration),
                    array('ID' => $chapter->ID),
                    array('%d'),
                    array('%d')
                );
                
                $updated_count++;
                $total_duration += $duration;
            }
        }
        
        // Update the lesson's total duration
        if ($total_duration > 0) {
            $wpdb->update(
                $lessons_table,
                array('duration' => $total_duration),
                array('ID' => $lesson_id),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success(array(
            'updated' => $updated_count,
            'total_duration' => $total_duration,
            'chapters_count' => count($chapters),
            'debug_info' => implode("\n", $debug_info)
        ));
    }
    
    /**
     * AJAX handler for calculating all chapter durations from Vimeo API
     */
    public function ajax_calculate_all_vimeo_durations() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $lesson_id = intval($_POST['lesson_id']);
        
        if (empty($lesson_id)) {
            wp_send_json_error('Invalid lesson ID');
        }
        
        global $wpdb;
        $database = new ALM_Database();
        $chapters_table = $database->get_table_name('chapters');
        $lessons_table = $database->get_table_name('lessons');
        
        // Get all chapters for this lesson that have Vimeo IDs
        $chapters = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, vimeo_id FROM {$chapters_table} WHERE lesson_id = %d AND vimeo_id > 0 AND vimeo_id IS NOT NULL",
            $lesson_id
        ));
        
        if (empty($chapters)) {
            wp_send_json_error('No chapters with Vimeo IDs found for this lesson');
        }
        
        $vimeo_api = new ALM_Vimeo_API();
        
        $updated_count = 0;
        $total_duration = 0;
        $debug_info = array();
        
        foreach ($chapters as $chapter) {
            $debug_info[] = "Chapter ID: {$chapter->ID}, Vimeo ID: {$chapter->vimeo_id}";
            
            // Get full metadata to see what we're getting
            $metadata = $vimeo_api->get_video_metadata($chapter->vimeo_id);
            $duration = false;
            
            if ($metadata !== false && isset($metadata['duration'])) {
                $duration = intval($metadata['duration']);
                $debug_info[] = "  - Metadata found. Duration: {$duration}, Status: " . (isset($metadata['status']) ? $metadata['status'] : 'unknown');
                
                // Check if video is private/unavailable
                if (isset($metadata['status']) && $metadata['status'] !== 'available') {
                    $debug_info[] = "  - Video status: {$metadata['status']} (may require authentication)";
                }
            } else {
                $debug_info[] = "  - Metadata fetch failed or duration missing";
                if ($metadata !== false) {
                    $debug_info[] = "  - Available fields: " . implode(', ', array_keys($metadata));
                }
            }
            
            if ($duration !== false && $duration > 0) {
                // Update the chapter duration
                $update_result = $wpdb->update(
                    $chapters_table,
                    array('duration' => $duration),
                    array('ID' => $chapter->ID),
                    array('%d'),
                    array('%d')
                );
                
                $debug_info[] = "  - Update result: " . ($update_result !== false ? "success" : "failed");
                
                $updated_count++;
                $total_duration += $duration;
            } else {
                $debug_info[] = "  - Update skipped: duration is " . ($duration === false ? "false/error" : "0 or invalid");
            }
        }
        
        // Log debug info
        error_log("ALM Vimeo Duration Debug: " . implode("\n", $debug_info));
        
        // Update the lesson's total duration
        if ($total_duration > 0) {
            $wpdb->update(
                $lessons_table,
                array('duration' => $total_duration),
                array('ID' => $lesson_id),
                array('%d'),
                array('%d')
            );
        }
        
        wp_send_json_success(array(
            'updated' => $updated_count,
            'total_duration' => $total_duration,
            'chapters_count' => count($chapters)
        ));
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
     * AJAX handler for updating favorites order
     */
    public function ajax_update_favorites_order() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_favorites_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $order = isset($_POST['order']) ? array_map('intval', $_POST['order']) : array();
        $table_type = isset($_POST['table_type']) ? sanitize_text_field($_POST['table_type']) : '';
        
        if (empty($order)) {
            wp_send_json_error('No order provided');
        }
        
        global $wpdb;
        
        // Determine table name
        if ($table_type === 'jph_lesson_favorites') {
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        } elseif ($table_type === 'jf_favorites') {
            $table_name = $wpdb->prefix . 'jf_favorites';
        } else {
            // Default fallback
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        }
        
        // Check if display_order column exists, if not we'll skip the order save
        // For now, just return success since we don't have the column yet
        // The order will be maintained by the frontend DOM order
        // TODO: Add display_order column to favorites tables if needed
        
        wp_send_json_success('Order updated successfully');
    }
    
    /**
     * AJAX handler for deleting a favorite
     */
    public function ajax_delete_favorite() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'alm_favorites_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $favorite_id = isset($_POST['favorite_id']) ? intval($_POST['favorite_id']) : 0;
        $table_type = isset($_POST['table_type']) ? sanitize_text_field($_POST['table_type']) : '';
        
        if (empty($favorite_id)) {
            wp_send_json_error('No favorite ID provided');
        }
        
        global $wpdb;
        
        // Determine table name
        if ($table_type === 'jph_lesson_favorites') {
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        } elseif ($table_type === 'jf_favorites') {
            $table_name = $wpdb->prefix . 'jf_favorites';
        } else {
            // Default fallback
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        }
        
        // Check if user owns this favorite
        $user_id = get_current_user_id();
        $favorite = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
            $favorite_id,
            $user_id
        ));
        
        if (!$favorite) {
            wp_send_json_error('Favorite not found or access denied');
        }
        
        // Delete the favorite
        $deleted = $wpdb->delete(
            $table_name,
            array('id' => $favorite_id, 'user_id' => $user_id),
            array('%d', '%d')
        );
        
        if ($deleted) {
            wp_send_json_success('Favorite deleted successfully');
        } else {
            wp_send_json_error('Failed to delete favorite');
        }
    }
    
    /**
     * AJAX handler for toggling resource favorites
     */
    public function ajax_toggle_resource_favorite() {
        try {
            // Check if user is logged in first
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error('User not logged in');
                return;
            }
            
            // Verify nonce
            if (!isset($_POST['nonce'])) {
                wp_send_json_error('Security check failed: nonce not set');
                return;
            }
            
            $nonce_check = wp_verify_nonce($_POST['nonce'], 'alm_resource_favorite_nonce');
            if (!$nonce_check) {
                wp_send_json_error('Security check failed');
                return;
            }
            
            if (!isset($_POST['resource_url'])) {
                wp_send_json_error('Resource URL is required');
                return;
            }
            
            $resource_url = sanitize_text_field($_POST['resource_url']);
            $is_favorite = isset($_POST['is_favorite']) ? intval($_POST['is_favorite']) : 0;
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'jph_lesson_favorites';
        
        if ($is_favorite) {
            // Add favorite
            $resource_name = isset($_POST['resource_name']) ? sanitize_text_field($_POST['resource_name']) : 'Resource';
            $resource_link = isset($_POST['resource_link']) ? sanitize_text_field($_POST['resource_link']) : '';
            $resource_type = isset($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '';
            
            // Check if already favorited
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND url = %s",
                $user_id,
                $resource_url
            ));
            
            if ($existing) {
                wp_send_json_success(array('id' => $existing, 'message' => 'Already favorited'));
                return;
            }
            
            // Insert new favorite - build array dynamically to handle optional fields
            $insert_data = array(
                'user_id' => $user_id,
                'title' => $resource_name,
                'url' => $resource_url,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );
            $insert_format = array('%d', '%s', '%s', '%s', '%s');
            
            // Add resource_link if provided
            if (!empty($resource_link)) {
                $insert_data['resource_link'] = $resource_link;
                $insert_format[] = '%s';
            }
            
            // Add resource_type if provided
            if (!empty($resource_type)) {
                $insert_data['resource_type'] = $resource_type;
                $insert_format[] = '%s';
            }
            
            // Add category (try to set it, but it might be optional)
            $insert_data['category'] = 'lesson';
            $insert_format[] = '%s';
            
            $result = $wpdb->insert($table_name, $insert_data, $insert_format);
            
            if ($result !== false) {
                $favorite_id = $wpdb->insert_id;
                wp_send_json_success(array('id' => $favorite_id, 'message' => 'Added to favorites'));
            } else {
                $error = $wpdb->last_error ? $wpdb->last_error : 'Database insert failed';
                wp_send_json_error($error);
            }
        } else {
            // Remove favorite
            $deleted = $wpdb->delete(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'url' => $resource_url
                ),
                array('%d', '%s')
            );
            
            if ($deleted) {
                wp_send_json_success(array('message' => 'Removed from favorites'));
            } else {
                wp_send_json_error('Failed to remove favorite');
            }
        }
        } catch (Exception $e) {
            wp_send_json_error('An error occurred: ' . $e->getMessage());
        }
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
new Academy_Lesson_Manager();
