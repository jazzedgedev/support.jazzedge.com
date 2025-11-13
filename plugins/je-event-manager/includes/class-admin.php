<?php
/**
 * Admin Class for JE Event Manager
 * Handles bulk copy functionality for je_event post type
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_Event_Manager_Admin {
    
    /**
     * ACF field keys for events
     */
    private $acf_fields = array(
        'je_event_zoom_link' => 'Zoom Link',
        'je_event_start' => 'Event Start',
        'je_event_end' => 'Event End',
        'je_event_resource_repeater' => 'Resource Repeater',
        'je_event_replay_vimeo_id' => 'Replay Vimeo ID',
        'je_event_bunny_url' => 'Bunny URL',
        'je_event_registration' => 'Registration',
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add bulk copy button to posts list page
        add_action('restrict_manage_posts', array($this, 'add_bulk_copy_button'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_jeem_bulk_copy_event', array($this, 'ajax_bulk_copy_event'));
        add_action('wp_ajax_jeem_get_events_list', array($this, 'ajax_get_events_list'));
    }
    
    /**
     * Add bulk copy button above the posts table
     */
    public function add_bulk_copy_button($post_type) {
        if ($post_type !== 'je_event') {
            return;
        }
        ?>
        <div class="jeem-bulk-copy-wrapper" style="margin: 10px 0;">
            <button type="button" class="button button-primary" id="jeem-open-bulk-copy-modal">
                <span class="dashicons dashicons-calendar-alt" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php _e('Bulk Copy Event', 'je-event-manager'); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only enqueue on je_event post list page
        if ($hook !== 'edit.php' || !isset($_GET['post_type']) || $_GET['post_type'] !== 'je_event') {
            return;
        }
        
        // Include modals on the posts list page
        add_action('admin_footer', array($this, 'include_modals'));
        
        wp_enqueue_style(
            'je-event-manager-admin',
            JE_EVENT_MANAGER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JE_EVENT_MANAGER_VERSION
        );
        
        wp_enqueue_script(
            'je-event-manager-admin',
            JE_EVENT_MANAGER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            JE_EVENT_MANAGER_VERSION,
            true
        );
        
        // Enqueue jQuery UI datepicker styles
        wp_enqueue_style('jquery-ui-datepicker', 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css', array(), '1.13.2');
        
        wp_localize_script('je-event-manager-admin', 'jeEventManager', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('je_event_manager_nonce'),
        ));
    }
    
    /**
     * AJAX: Get events list for dropdown
     */
    public function ajax_get_events_list() {
        check_ajax_referer('je_event_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $args = array(
            'post_type' => 'je_event',
            'post_status' => 'any',
            'posts_per_page' => 100, // Get more to ensure we have 50 with dates
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $events = get_posts($args);
        $events_with_dates = array();
        
        foreach ($events as $event) {
            // Get start date for sorting
            $start_timestamp = 0;
            if (function_exists('get_field')) {
                $start_value = get_field('je_event_start', $event->ID);
            } else {
                $start_value = get_post_meta($event->ID, 'je_event_start', true);
            }
            
            // Convert to timestamp for sorting
            if ($start_value) {
                if ($start_value instanceof DateTime) {
                    $start_timestamp = $start_value->getTimestamp();
                } elseif (is_numeric($start_value)) {
                    $start_timestamp = intval($start_value);
                } elseif (is_string($start_value)) {
                    $ts = strtotime($start_value);
                    if ($ts) {
                        $start_timestamp = $ts;
                    }
                }
            }
            
            // Only include events with valid start dates
            if ($start_timestamp > 0) {
                // Format start date for display
                $start_date = date('Y-m-d H:i', $start_timestamp);
                
                // Get membership level
                $membership_levels = wp_get_post_terms($event->ID, 'membership-level', array('fields' => 'names'));
                $membership_text = '';
                if (!is_wp_error($membership_levels) && !empty($membership_levels)) {
                    $membership_text = ' - ' . implode(', ', $membership_levels);
                }
                
                $display_title = $event->post_title;
                if ($start_date) {
                    $display_title .= ' (' . $start_date . ')';
                }
                if ($membership_text) {
                    $display_title .= $membership_text;
                }
                
                $events_with_dates[] = array(
                    'id' => $event->ID,
                    'title' => $display_title,
                    'post_title' => $event->post_title,
                    'start_timestamp' => $start_timestamp,
                );
            }
        }
        
        // Sort by start date timestamp (newest first)
        usort($events_with_dates, function($a, $b) {
            return $b['start_timestamp'] - $a['start_timestamp'];
        });
        
        // Limit to 50
        $events_list = array_slice($events_with_dates, 0, 50);
        
        // Remove timestamp from final output
        foreach ($events_list as &$event) {
            unset($event['start_timestamp']);
        }
        
        wp_send_json_success($events_list);
    }
    
    /**
     * Include modals in admin footer
     */
    public function include_modals() {
        include JE_EVENT_MANAGER_PLUGIN_DIR . 'templates/modals.php';
    }
    
    /**
     * AJAX: Bulk copy event
     */
    public function ajax_bulk_copy_event() {
        check_ajax_referer('je_event_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $interval = isset($_POST['interval']) ? intval($_POST['interval']) : 0;
        $interval_unit = isset($_POST['interval_unit']) ? sanitize_text_field($_POST['interval_unit']) : 'weeks';
        $count = isset($_POST['count']) ? intval($_POST['count']) : 1;
        $copy_multiple = isset($_POST['copy_multiple']) && $_POST['copy_multiple'] === 'true';
        $source_ids = isset($_POST['source_ids']) ? array_map('intval', explode(',', $_POST['source_ids'])) : array();
        
        // If copying multiple events, copy each selected event 'count' times
        if ($copy_multiple && !empty($source_ids)) {
            $created_ids = array();
            
            // Parse the start date to ensure proper format
            try {
                $date_obj = new DateTime($start_date);
                $base_date = $date_obj->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $base_date = $start_date;
            }
            
            // For each selected event, create 'count' copies
            foreach ($source_ids as $source_id) {
                $current_date = $base_date;
                
                // Create 'count' copies of this event
                for ($i = 0; $i < $count; $i++) {
                    $new_id = $this->copy_event($source_id, $current_date);
                    
                    if (!is_wp_error($new_id)) {
                        $created_ids[] = $new_id;
                    }
                    
                    // Calculate next date for next copy (only if not the last copy)
                    if ($i < $count - 1) {
                        try {
                            $date_obj = new DateTime($current_date);
                            if ($interval_unit === 'weeks') {
                                $date_obj->modify('+' . $interval . ' weeks');
                            } elseif ($interval_unit === 'days') {
                                $date_obj->modify('+' . $interval . ' days');
                            } elseif ($interval_unit === 'months') {
                                $date_obj->modify('+' . $interval . ' months');
                            }
                            $current_date = $date_obj->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            break;
                        }
                    }
                }
            }
            
            wp_send_json_success(array(
                'message' => sprintf('%d events created', count($created_ids)),
                'created_ids' => $created_ids
            ));
            return;
        }
        
        // Original single event bulk copy logic
        if (!$source_id || !$start_date || !$interval || !$count) {
            wp_send_json_error(array('message' => 'All fields are required'));
        }
        
        $created_ids = array();
        $current_date = $start_date;
        
        // Parse the start date to ensure proper format
        try {
            $date_obj = new DateTime($start_date);
            $current_date = $date_obj->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $current_date = $start_date;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $new_id = $this->copy_event($source_id, $current_date);
            
            if (!is_wp_error($new_id)) {
                $created_ids[] = $new_id;
            }
            
            // Calculate next date (only if not the last iteration)
            if ($i < $count - 1) {
                try {
                    $date_obj = new DateTime($current_date);
                    if ($interval_unit === 'weeks') {
                        $date_obj->modify('+' . $interval . ' weeks');
                    } elseif ($interval_unit === 'days') {
                        $date_obj->modify('+' . $interval . ' days');
                    } elseif ($interval_unit === 'months') {
                        $date_obj->modify('+' . $interval . ' months');
                    }
                    $current_date = $date_obj->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    break;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf('%d events created', count($created_ids)),
            'created_ids' => $created_ids
        ));
    }
    
    /**
     * Copy an event
     */
    private function copy_event($source_id, $new_start_date = '') {
        $source_post = get_post($source_id);
        
        if (!$source_post || $source_post->post_type !== 'je_event') {
            return new WP_Error('invalid_event', 'Invalid source event');
        }
        
        // Create new post
        $new_post_data = array(
            'post_title' => $source_post->post_title,
            'post_content' => $source_post->post_content,
            'post_excerpt' => $source_post->post_excerpt,
            'post_type' => 'je_event',
            'post_status' => $source_post->post_status,
        );
        
        $new_id = wp_insert_post($new_post_data);
        
        if (is_wp_error($new_id)) {
            return $new_id;
        }
        
        // Copy all ACF fields
        foreach ($this->acf_fields as $key => $label) {
            // Skip bunny_url and replay_vimeo_id - do not copy these fields
            if ($key === 'je_event_bunny_url' || $key === 'je_event_replay_vimeo_id') {
                continue;
            }
            
            // Check if ACF function exists, otherwise use get_post_meta
            if (function_exists('get_field')) {
                $value = get_field($key, $source_id);
            } else {
                $value = get_post_meta($source_id, $key, true);
            }
            
            if ($key === 'je_event_start') {
                if ($new_start_date) {
                    // Update start date with new date
                    $start_value = $new_start_date;
                } else {
                    // Copy original start date
                    $start_value = $value;
                }
                
                // Try to use ACF update_field, otherwise use update_post_meta
                if (function_exists('update_field')) {
                    update_field($key, $start_value, $new_id);
                } else {
                    update_post_meta($new_id, $key, $start_value);
                }
                
                // Always set end date to 1 hour after start date (whether new or copied)
                try {
                    $timezone = wp_timezone();
                    $start_dt = new DateTime($start_value, $timezone);
                    $new_end_dt = clone $start_dt;
                    $new_end_dt->modify('+1 hour');
                    $new_end_value = $new_end_dt->format('Y-m-d H:i:s');
                    
                    if (function_exists('update_field')) {
                        update_field('je_event_end', $new_end_value, $new_id);
                    } else {
                        update_post_meta($new_id, 'je_event_end', $new_end_value);
                    }
                } catch (Exception $e) {
                    // If date parsing fails, try to set end date anyway
                    try {
                        $start_dt = new DateTime($start_value, wp_timezone());
                        $new_end_dt = clone $start_dt;
                        $new_end_dt->modify('+1 hour');
                        $new_end_value = $new_end_dt->format('Y-m-d H:i:s');
                        
                        if (function_exists('update_field')) {
                            update_field('je_event_end', $new_end_value, $new_id);
                        } else {
                            update_post_meta($new_id, 'je_event_end', $new_end_value);
                        }
                    } catch (Exception $e2) {
                        // Log error but continue
                        error_log('JE Event Manager: Failed to set end date: ' . $e2->getMessage());
                    }
                }
            } elseif ($key === 'je_event_end') {
                // Skip copying je_event_end - it will be set to 1 hour after start date
                // This is handled when we process je_event_start
                continue;
            } elseif ($key === 'je_event_resource_repeater' && $value) {
                // Handle repeater field
                if (function_exists('update_field')) {
                    update_field($key, $value, $new_id);
                } else {
                    // For non-ACF, copy the meta value
                    $meta_value = get_post_meta($source_id, $key, true);
                    if ($meta_value) {
                        update_post_meta($new_id, $key, $meta_value);
                    }
                }
            } else {
                if ($value !== false && $value !== null) {
                    if (function_exists('update_field')) {
                        update_field($key, $value, $new_id);
                    } else {
                        update_post_meta($new_id, $key, $value);
                    }
                }
            }
        }
        
        // Copy taxonomies
        $taxonomies = array('membership-level', 'event-type', 'teacher');
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($source_id, $taxonomy, array('fields' => 'ids'));
            if (!is_wp_error($terms) && !empty($terms)) {
                wp_set_post_terms($new_id, $terms, $taxonomy);
            }
        }
        
        // Copy post meta (for any additional fields)
        $meta_keys = get_post_meta($source_id);
        foreach ($meta_keys as $key => $values) {
            // Skip ACF fields (they're already handled)
            if (strpos($key, 'je_event_') === 0) {
                continue;
            }
            // Skip bunny_url and replay_vimeo_id explicitly (in case they're stored differently)
            if ($key === 'je_event_bunny_url' || $key === 'je_event_replay_vimeo_id') {
                continue;
            }
            // Skip internal WordPress meta
            if (strpos($key, '_') === 0 && $key !== '_thumbnail_id') {
                continue;
            }
            foreach ($values as $value) {
                add_post_meta($new_id, $key, maybe_unserialize($value));
            }
        }
        
        return $new_id;
    }
}
