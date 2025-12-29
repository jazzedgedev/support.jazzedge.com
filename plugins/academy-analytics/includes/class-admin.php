<?php
/**
 * Admin Class for Academy Analytics
 * 
 * Handles admin interface, CRUD operations, and reporting
 * 
 * @package Academy_Analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

class Academy_Analytics_Admin {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Academy_Analytics_Database();
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Academy Analytics', 'academy-analytics'),
            __('Analytics', 'academy-analytics'),
            'manage_options',
            'academy-analytics',
            array($this, 'render_main_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'academy-analytics',
            __('Events', 'academy-analytics'),
            __('Events', 'academy-analytics'),
            'manage_options',
            'academy-analytics',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'academy-analytics',
            __('Reports', 'academy-analytics'),
            __('Reports', 'academy-analytics'),
            'manage_options',
            'academy-analytics-reports',
            array($this, 'render_reports_page')
        );
        
        add_submenu_page(
            'academy-analytics',
            __('KPI Dashboard', 'academy-analytics'),
            __('KPI Dashboard', 'academy-analytics'),
            'manage_options',
            'academy-analytics-dashboard',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'academy-analytics',
            __('Settings', 'academy-analytics'),
            __('Settings', 'academy-analytics'),
            'manage_options',
            'academy-analytics-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render main page (events list)
     */
    public function render_main_page() {
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_form_submission();
            return;
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        echo '<div class="wrap academy-analytics-wrap">';
        echo '<h1>' . __('Academy Analytics', 'academy-analytics') . '</h1>';
        
        // Show messages
        if (isset($_GET['message'])) {
            $this->show_message($_GET['message']);
        }
        
        switch ($action) {
            case 'add':
                $this->render_add_page();
                break;
            case 'edit':
                $this->render_edit_page($id);
                break;
            case 'delete':
                $this->handle_delete($id);
                break;
            case 'view':
                $this->render_view_page($id);
                break;
            default:
                $this->render_list_page();
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Render list page
     */
    private function render_list_page() {
        // Get filter parameters
        $filters = array(
            'event_type' => isset($_GET['event_type']) ? sanitize_text_field($_GET['event_type']) : '',
            'email' => isset($_GET['email']) ? sanitize_text_field($_GET['email']) : '',
            'form_name' => isset($_GET['form_name']) ? sanitize_text_field($_GET['form_name']) : '',
            'page_url' => isset($_GET['page_url']) ? sanitize_text_field($_GET['page_url']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'search' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
            'per_page' => isset($_GET['per_page']) ? intval($_GET['per_page']) : 20,
            'page' => isset($_GET['paged']) ? intval($_GET['paged']) : 1
        );
        
        // Convert date filters from WordPress timezone to UTC for database queries
        $wp_timezone = wp_timezone();
        $utc_timezone = new DateTimeZone('UTC');
        
        if (!empty($filters['date_from'])) {
            // If it's just a date (Y-m-d), add time and convert to UTC
            if (strlen($filters['date_from']) === 10) {
                $wp_datetime = new DateTime($filters['date_from'] . ' 00:00:00', $wp_timezone);
                $wp_datetime->setTimezone($utc_timezone);
                $filters['date_from'] = $wp_datetime->format('Y-m-d H:i:s');
            }
        }
        
        if (!empty($filters['date_to'])) {
            // If it's just a date (Y-m-d), add time and convert to UTC
            if (strlen($filters['date_to']) === 10) {
                $wp_datetime = new DateTime($filters['date_to'] . ' 23:59:59', $wp_timezone);
                $wp_datetime->setTimezone($utc_timezone);
                $filters['date_to'] = $wp_datetime->format('Y-m-d H:i:s');
            }
        }
        
        // Get events
        $result = $this->database->get_events($filters);
        $events = $result['events'];
        $total = $result['total'];
        $pages = $result['pages'];
        
        // Render filters
        $this->render_filters($filters);
        
        // Render bulk actions
        echo '<form method="post" action="" id="events-form">';
        wp_nonce_field('academy_analytics_bulk_action', 'academy_analytics_nonce');
        
        // Table
        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<thead>';
        echo '<tr>';
        echo '<td id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all" /></td>';
        echo '<th scope="col" class="manage-column column-id">' . __('ID', 'academy-analytics') . '</th>';
        echo '<th scope="col" class="manage-column column-event-type">' . __('Event Type', 'academy-analytics') . '</th>';
        echo '<th scope="col" class="manage-column column-email">' . __('Email', 'academy-analytics') . '</th>';
        echo '<th scope="col" class="manage-column column-form-name">' . __('Form/Page', 'academy-analytics') . '</th>';
        echo '<th scope="col" class="manage-column column-created">' . __('Date', 'academy-analytics') . '</th>';
        echo '<th scope="col" class="manage-column column-actions">' . __('Actions', 'academy-analytics') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($events)) {
            echo '<tr><td colspan="7" class="no-items">' . __('No events found.', 'academy-analytics') . '</td></tr>';
        } else {
            foreach ($events as $event) {
                $this->render_event_row($event);
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // Bulk actions
        echo '<div class="tablenav bottom">';
        echo '<div class="alignleft actions bulkactions">';
        echo '<select name="bulk_action" id="bulk-action-selector-bottom">';
        echo '<option value="">' . __('Bulk Actions', 'academy-analytics') . '</option>';
        echo '<option value="delete">' . __('Delete', 'academy-analytics') . '</option>';
        echo '</select>';
        echo '<input type="submit" class="button action" value="' . __('Apply', 'academy-analytics') . '" />';
        echo '</div>';
        
        // Pagination
        if ($pages > 1) {
            echo '<div class="tablenav-pages">';
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $pages,
                'current' => $filters['page']
            ));
            echo $page_links;
            echo '</div>';
        }
        
        echo '</div>'; // tablenav
        echo '</form>';
    }
    
    /**
     * Render event row
     */
    private function render_event_row($event) {
        $form_page = !empty($event->form_name) ? esc_html($event->form_name) : (!empty($event->page_title) ? esc_html($event->page_title) : '—');
        
        echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="event_ids[]" value="' . esc_attr($event->id) . '" /></th>';
        echo '<td class="column-id">' . esc_html($event->id) . '</td>';
        echo '<td class="column-event-type"><span class="event-type-badge event-type-' . esc_attr($event->event_type) . '">' . esc_html(ucwords(str_replace('_', ' ', $event->event_type))) . '</span></td>';
        echo '<td class="column-email">' . (!empty($event->email) ? esc_html($event->email) : '—') . '</td>';
        echo '<td class="column-form-name">' . $form_page . '</td>';
        echo '<td class="column-created">' . esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $event->created_at)) . '</td>';
        echo '<td class="column-actions">';
        echo '<a href="' . esc_url(add_query_arg(array('action' => 'view', 'id' => $event->id))) . '" class="button button-small">' . __('View', 'academy-analytics') . '</a> ';
        echo '<a href="' . esc_url(add_query_arg(array('action' => 'edit', 'id' => $event->id))) . '" class="button button-small">' . __('Edit', 'academy-analytics') . '</a> ';
        echo '<a href="' . esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $event->id)), 'delete_event_' . $event->id)) . '" class="button button-small button-link-delete">' . __('Delete', 'academy-analytics') . '</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    /**
     * Render filters
     */
    private function render_filters($filters) {
        echo '<div class="academy-analytics-filters">';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="academy-analytics" />';
        
        echo '<div class="filter-row">';
        
        // Event Type
        $event_types = academy_analytics()->get_event_types();
        echo '<select name="event_type" style="margin-right: 10px;">';
        echo '<option value="">' . __('All Event Types', 'academy-analytics') . '</option>';
        foreach ($event_types as $type_id => $type_data) {
            echo '<option value="' . esc_attr($type_id) . '"' . selected($filters['event_type'], $type_id, false) . '>' . esc_html($type_data['label']) . '</option>';
        }
        echo '</select>';
        
        // Email
        echo '<input type="text" name="email" placeholder="' . __('Email', 'academy-analytics') . '" value="' . esc_attr($filters['email']) . '" style="margin-right: 10px;" />';
        
        // Form Name
        echo '<input type="text" name="form_name" placeholder="' . __('Form Name', 'academy-analytics') . '" value="' . esc_attr($filters['form_name']) . '" style="margin-right: 10px;" />';
        
        // Page URL
        echo '<input type="text" name="page_url" placeholder="' . __('Page URL', 'academy-analytics') . '" value="' . esc_attr($filters['page_url']) . '" style="margin-right: 10px;" />';
        
        // Date From
        echo '<input type="date" name="date_from" value="' . esc_attr($filters['date_from']) . '" placeholder="' . __('From Date', 'academy-analytics') . '" style="margin-right: 10px;" />';
        
        // Date To
        echo '<input type="date" name="date_to" value="' . esc_attr($filters['date_to']) . '" placeholder="' . __('To Date', 'academy-analytics') . '" style="margin-right: 10px;" />';
        
        // Search
        echo '<input type="text" name="s" placeholder="' . __('Search...', 'academy-analytics') . '" value="' . esc_attr($filters['search']) . '" style="margin-right: 10px;" />';
        
        // Submit
        echo '<input type="submit" class="button" value="' . __('Filter', 'academy-analytics') . '" />';
        echo '<a href="' . esc_url(admin_url('admin.php?page=academy-analytics')) . '" class="button">' . __('Reset', 'academy-analytics') . '</a>';
        
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render add page
     */
    private function render_add_page() {
        echo '<h2>' . __('Add New Event', 'academy-analytics') . '</h2>';
        $this->render_event_form();
    }
    
    /**
     * Render edit page
     */
    private function render_edit_page($id) {
        $event = $this->database->get_event($id);
        
        if (!$event) {
            echo '<div class="notice notice-error"><p>' . __('Event not found.', 'academy-analytics') . '</p></div>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=academy-analytics')) . '" class="button">' . __('Back to Events', 'academy-analytics') . '</a>';
            return;
        }
        
        echo '<h2>' . __('Edit Event', 'academy-analytics') . '</h2>';
        $this->render_event_form($event);
    }
    
    /**
     * Render view page
     */
    private function render_view_page($id) {
        $event = $this->database->get_event($id);
        
        if (!$event) {
            echo '<div class="notice notice-error"><p>' . __('Event not found.', 'academy-analytics') . '</p></div>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=academy-analytics')) . '" class="button">' . __('Back to Events', 'academy-analytics') . '</a>';
            return;
        }
        
        echo '<h2>' . __('Event Details', 'academy-analytics') . '</h2>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=academy-analytics')) . '" class="button" style="margin-bottom: 20px;">' . __('Back to Events', 'academy-analytics') . '</a>';
        
        echo '<table class="form-table">';
        echo '<tr><th>' . __('ID', 'academy-analytics') . '</th><td>' . esc_html($event->id) . '</td></tr>';
        echo '<tr><th>' . __('Event Type', 'academy-analytics') . '</th><td>' . esc_html(ucwords(str_replace('_', ' ', $event->event_type))) . '</td></tr>';
        echo '<tr><th>' . __('User ID', 'academy-analytics') . '</th><td>' . (!empty($event->user_id) ? esc_html($event->user_id) : '—') . '</td></tr>';
        echo '<tr><th>' . __('Email', 'academy-analytics') . '</th><td>' . (!empty($event->email) ? esc_html($event->email) : '—') . '</td></tr>';
        echo '<tr><th>' . __('Form Name', 'academy-analytics') . '</th><td>' . (!empty($event->form_name) ? esc_html($event->form_name) : '—') . '</td></tr>';
        echo '<tr><th>' . __('Page Title', 'academy-analytics') . '</th><td>' . (!empty($event->page_title) ? esc_html($event->page_title) : '—') . '</td></tr>';
        echo '<tr><th>' . __('Page URL', 'academy-analytics') . '</th><td>' . (!empty($event->page_url) ? '<a href="' . esc_url($event->page_url) . '" target="_blank">' . esc_html($event->page_url) . '</a>' : '—') . '</td></tr>';
        echo '<tr><th>' . __('Referrer', 'academy-analytics') . '</th><td>' . (!empty($event->referrer) ? '<a href="' . esc_url($event->referrer) . '" target="_blank">' . esc_html($event->referrer) . '</a>' : '—') . '</td></tr>';
        echo '<tr><th>' . __('IP Address', 'academy-analytics') . '</th><td>' . (!empty($event->ip_address) ? esc_html($event->ip_address) : '—') . '</td></tr>';
        echo '<tr><th>' . __('Created At', 'academy-analytics') . '</th><td>' . esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $event->created_at)) . '</td></tr>';
        
        if (!empty($event->data) && is_array($event->data)) {
            echo '<tr><th>' . __('Additional Data', 'academy-analytics') . '</th><td><pre style="max-height: 400px; overflow: auto;">' . esc_html(json_encode($event->data, JSON_PRETTY_PRINT)) . '</pre></td></tr>';
        }
        
        echo '</table>';
        
        echo '<p>';
        echo '<a href="' . esc_url(add_query_arg(array('action' => 'edit', 'id' => $id))) . '" class="button button-primary">' . __('Edit', 'academy-analytics') . '</a> ';
        echo '<a href="' . esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $id)), 'delete_event_' . $id)) . '" class="button button-link-delete">' . __('Delete', 'academy-analytics') . '</a>';
        echo '</p>';
    }
    
    /**
     * Render event form
     */
    private function render_event_form($event = null) {
        $is_edit = !empty($event);
        
        echo '<form method="post" action="">';
        wp_nonce_field('academy_analytics_save_event', 'academy_analytics_nonce');
        
        if ($is_edit) {
            echo '<input type="hidden" name="event_id" value="' . esc_attr($event->id) . '" />';
        }
        
        echo '<table class="form-table">';
        
        echo '<tr>';
        echo '<th><label for="event_type">' . __('Event Type', 'academy-analytics') . '</label></th>';
        echo '<td>';
        $event_types = academy_analytics()->get_event_types();
        $current_type = $is_edit ? $event->event_type : '';
        echo '<select name="event_type" id="event_type" required>';
        echo '<option value="">' . __('Select...', 'academy-analytics') . '</option>';
        foreach ($event_types as $type_id => $type_data) {
            echo '<option value="' . esc_attr($type_id) . '"' . selected($current_type, $type_id, false) . '>' . esc_html($type_data['label']) . '</option>';
        }
        // Allow custom event types that aren't in the list
        if ($is_edit && !isset($event_types[$current_type])) {
            echo '<option value="' . esc_attr($current_type) . '" selected>' . esc_html(ucwords(str_replace('_', ' ', $current_type))) . ' (Custom)</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('You can also type a custom event type if it\'s not in the list.', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="user_id">' . __('User ID', 'academy-analytics') . '</label></th>';
        echo '<td><input type="number" name="user_id" id="user_id" value="' . esc_attr($is_edit ? $event->user_id : '') . '" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="email">' . __('Email', 'academy-analytics') . '</label></th>';
        echo '<td><input type="email" name="email" id="email" value="' . esc_attr($is_edit ? $event->email : '') . '" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="form_name">' . __('Form Name', 'academy-analytics') . '</label></th>';
        echo '<td><input type="text" name="form_name" id="form_name" value="' . esc_attr($is_edit ? $event->form_name : '') . '" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="page_title">' . __('Page Title', 'academy-analytics') . '</label></th>';
        echo '<td><input type="text" name="page_title" id="page_title" value="' . esc_attr($is_edit ? $event->page_title : '') . '" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="page_url">' . __('Page URL', 'academy-analytics') . '</label></th>';
        echo '<td><input type="url" name="page_url" id="page_url" value="' . esc_attr($is_edit ? $event->page_url : '') . '" style="width: 100%;" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="referrer">' . __('Referrer', 'academy-analytics') . '</label></th>';
        echo '<td><input type="url" name="referrer" id="referrer" value="' . esc_attr($is_edit ? $event->referrer : '') . '" style="width: 100%;" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="ip_address">' . __('IP Address', 'academy-analytics') . '</label></th>';
        echo '<td><input type="text" name="ip_address" id="ip_address" value="' . esc_attr($is_edit ? $event->ip_address : '') . '" /></td>';
        echo '</tr>';
        
        if ($is_edit && !empty($event->data) && is_array($event->data)) {
            echo '<tr>';
            echo '<th><label for="data">' . __('Additional Data (JSON)', 'academy-analytics') . '</label></th>';
            echo '<td><textarea name="data" id="data" rows="10" style="width: 100%; font-family: monospace;">' . esc_textarea(json_encode($event->data, JSON_PRETTY_PRINT)) . '</textarea></td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="save_event" class="button button-primary" value="' . esc_attr($is_edit ? __('Update Event', 'academy-analytics') : __('Add Event', 'academy-analytics')) . '" />';
        echo ' <a href="' . esc_url(admin_url('admin.php?page=academy-analytics')) . '" class="button">' . __('Cancel', 'academy-analytics') . '</a>';
        echo '</p>';
        
        echo '</form>';
    }
    
    /**
     * Handle form submission
     */
    private function handle_form_submission() {
        // Handle bulk actions
        if (isset($_POST['bulk_action']) && !empty($_POST['bulk_action']) && isset($_POST['event_ids']) && is_array($_POST['event_ids'])) {
            // Check nonce for bulk actions
            if (!isset($_POST['academy_analytics_nonce']) || !wp_verify_nonce($_POST['academy_analytics_nonce'], 'academy_analytics_bulk_action')) {
                wp_die(__('Security check failed.', 'academy-analytics'));
            }
            
            if ($_POST['bulk_action'] === 'delete') {
                $deleted = $this->database->delete_events($_POST['event_ids']);
                wp_redirect(add_query_arg('message', 'bulk_deleted', admin_url('admin.php?page=academy-analytics')));
                exit;
            }
        }
        
        // Handle save event
        if (isset($_POST['save_event'])) {
            // Check nonce for save event
            if (!isset($_POST['academy_analytics_nonce']) || !wp_verify_nonce($_POST['academy_analytics_nonce'], 'academy_analytics_save_event')) {
                wp_die(__('Security check failed.', 'academy-analytics'));
            }
            $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
            
            $data = array(
                'event_type' => sanitize_text_field($_POST['event_type']),
                'user_id' => !empty($_POST['user_id']) ? intval($_POST['user_id']) : null,
                'email' => !empty($_POST['email']) ? sanitize_email($_POST['email']) : null,
                'form_name' => !empty($_POST['form_name']) ? sanitize_text_field($_POST['form_name']) : null,
                'page_title' => !empty($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : null,
                'page_url' => !empty($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : null,
                'referrer' => !empty($_POST['referrer']) ? esc_url_raw($_POST['referrer']) : null,
                'ip_address' => !empty($_POST['ip_address']) ? sanitize_text_field($_POST['ip_address']) : null,
            );
            
            // Handle JSON data
            if (!empty($_POST['data'])) {
                $json_data = json_decode(stripslashes($_POST['data']), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['data'] = $json_data;
                }
            }
            
            if ($event_id) {
                // Update
                $result = $this->database->update_event($event_id, $data);
                $message = $result ? 'updated' : 'error';
            } else {
                // Insert
                $result = $this->database->insert_event($data);
                $message = $result ? 'created' : 'error';
            }
            
            wp_redirect(add_query_arg('message', $message, admin_url('admin.php?page=academy-analytics')));
            exit;
        }
    }
    
    /**
     * Handle delete
     */
    private function handle_delete($id) {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_event_' . $id)) {
            wp_die(__('Security check failed.', 'academy-analytics'));
        }
        
        $result = $this->database->delete_event($id);
        $message = $result ? 'deleted' : 'error';
        
        wp_redirect(add_query_arg('message', $message, admin_url('admin.php?page=academy-analytics')));
        exit;
    }
    
    /**
     * Show message
     */
    private function show_message($message) {
        $class = 'notice-success';
        $text = '';
        
        switch ($message) {
            case 'created':
                $text = __('Event created successfully.', 'academy-analytics');
                break;
            case 'updated':
                $text = __('Event updated successfully.', 'academy-analytics');
                break;
            case 'deleted':
                $text = __('Event deleted successfully.', 'academy-analytics');
                break;
            case 'bulk_deleted':
                $text = __('Events deleted successfully.', 'academy-analytics');
                break;
            case 'error':
                $class = 'notice-error';
                $text = __('An error occurred.', 'academy-analytics');
                break;
        }
        
        if ($text) {
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
        }
    }
    
    /**
     * Render reports page
     */
    public function render_reports_page() {
        // Get time frame
        $time_frame = isset($_GET['time_frame']) ? sanitize_text_field($_GET['time_frame']) : '30';
        
        // Calculate date range
        $date_to = current_time('Y-m-d 23:59:59');
        $date_from = '';
        
        switch ($time_frame) {
            case '1':
                $date_from = date('Y-m-d 00:00:00', strtotime('-1 day'));
                break;
            case '7':
                $date_from = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case '14':
                $date_from = date('Y-m-d 00:00:00', strtotime('-14 days'));
                break;
            case '30':
                $date_from = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case '90':
                $date_from = date('Y-m-d 00:00:00', strtotime('-90 days'));
                break;
            case 'custom':
                $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) . ' 00:00:00' : date('Y-m-d 00:00:00', strtotime('-30 days'));
                $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) . ' 23:59:59' : current_time('Y-m-d 23:59:59');
                break;
        }
        
        // Get event type filter
        $event_type = isset($_GET['event_type']) ? sanitize_text_field($_GET['event_type']) : '';
        
        // Get stats
        $stats_args = array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'event_type' => $event_type
        );
        
        $stats = $this->database->get_stats($stats_args);
        
        echo '<div class="wrap academy-analytics-reports">';
        echo '<h1>' . __('Analytics Reports', 'academy-analytics') . '</h1>';
        
        // Time frame selector
        echo '<div class="academy-analytics-timeframe-selector" style="margin: 20px 0;">';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="academy-analytics-reports" />';
        
        echo '<label for="time_frame" style="margin-right: 10px;">' . __('Time Frame:', 'academy-analytics') . '</label>';
        echo '<select name="time_frame" id="time_frame" onchange="this.form.submit()" style="margin-right: 10px;">';
        echo '<option value="1"' . selected($time_frame, '1', false) . '>' . __('Past Day', 'academy-analytics') . '</option>';
        echo '<option value="7"' . selected($time_frame, '7', false) . '>' . __('Past 7 Days', 'academy-analytics') . '</option>';
        echo '<option value="14"' . selected($time_frame, '14', false) . '>' . __('Past 14 Days', 'academy-analytics') . '</option>';
        echo '<option value="30"' . selected($time_frame, '30', false) . '>' . __('Past 30 Days', 'academy-analytics') . '</option>';
        echo '<option value="90"' . selected($time_frame, '90', false) . '>' . __('Past 90 Days', 'academy-analytics') . '</option>';
        echo '<option value="custom"' . selected($time_frame, 'custom', false) . '>' . __('Custom Range', 'academy-analytics') . '</option>';
        echo '</select>';
        
        if ($time_frame === 'custom') {
            echo '<input type="date" name="date_from" value="' . esc_attr($date_from) . '" style="margin-right: 10px;" />';
            echo '<input type="date" name="date_to" value="' . esc_attr($date_to) . '" style="margin-right: 10px;" />';
        }
        
        echo '<select name="event_type" style="margin-right: 10px;">';
        $event_types = academy_analytics()->get_event_types();
        echo '<option value="">' . __('All Event Types', 'academy-analytics') . '</option>';
        foreach ($event_types as $type_id => $type_data) {
            echo '<option value="' . esc_attr($type_id) . '"' . selected($event_type, $type_id, false) . '>' . esc_html($type_data['label']) . '</option>';
        }
        echo '</select>';
        
        echo '<input type="submit" class="button" value="' . __('Update', 'academy-analytics') . '" />';
        echo '</form>';
        echo '</div>';
        
        // Stats cards
        echo '<div class="academy-analytics-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">';
        
        echo '<div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">' . __('Total Events', 'academy-analytics') . '</h3>';
        echo '<div style="font-size: 32px; font-weight: bold; color: #2271b1;">' . number_format($stats['total_events']) . '</div>';
        echo '</div>';
        
        echo '<div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">' . __('Unique Users', 'academy-analytics') . '</h3>';
        echo '<div style="font-size: 32px; font-weight: bold; color: #2271b1;">' . number_format($stats['unique_users']) . '</div>';
        echo '</div>';
        
        echo '<div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">' . __('Unique Emails', 'academy-analytics') . '</h3>';
        echo '<div style="font-size: 32px; font-weight: bold; color: #2271b1;">' . number_format($stats['unique_emails']) . '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Get time series data for charts
        $time_series_data = $this->database->get_time_series_data($stats_args);
        
        // Prepare chart data
        $chart_data = array(
            'time_series' => $time_series_data,
            'by_type' => $stats['by_type'],
            'top_forms' => $stats['top_forms'],
            'top_pages' => $stats['top_pages']
        );
        
        // Charts Section
        echo '<div class="academy-analytics-charts" style="margin-top: 30px;">';
        
        // Events Over Time Chart
        echo '<div class="academy-analytics-chart-container" style="background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<h2>' . __('Events Over Time', 'academy-analytics') . '</h2>';
        echo '<canvas id="events-over-time-chart" style="max-height: 400px;"></canvas>';
        echo '</div>';
        
        // Charts Grid
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 20px;">';
        
        // Events by Type Chart
        if (!empty($stats['by_type'])) {
            echo '<div class="academy-analytics-chart-container" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">';
            echo '<h2>' . __('Events by Type', 'academy-analytics') . '</h2>';
            echo '<canvas id="events-by-type-chart" style="max-height: 300px;"></canvas>';
            echo '</div>';
        }
        
        // Top Forms Chart
        if (!empty($stats['top_forms'])) {
            echo '<div class="academy-analytics-chart-container" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">';
            echo '<h2>' . __('Top Forms', 'academy-analytics') . '</h2>';
            echo '<canvas id="top-forms-chart" style="max-height: 300px;"></canvas>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Top Pages Chart
        if (!empty($stats['top_pages'])) {
            echo '<div class="academy-analytics-chart-container" style="background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">';
            echo '<h2>' . __('Top Pages', 'academy-analytics') . '</h2>';
            echo '<canvas id="top-pages-chart" style="max-height: 400px;"></canvas>';
            echo '</div>';
        }
        
        echo '</div>'; // End charts section
        
        // Tables Section (keep existing tables)
        // Events by type table
        if (!empty($stats['by_type'])) {
            echo '<h2>' . __('Events by Type', 'academy-analytics') . '</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>' . __('Event Type', 'academy-analytics') . '</th><th>' . __('Count', 'academy-analytics') . '</th></tr></thead>';
            echo '<tbody>';
            foreach ($stats['by_type'] as $type => $data) {
                echo '<tr>';
                echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $type))) . '</td>';
                echo '<td>' . number_format($data->count) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        
        // Top forms table
        if (!empty($stats['top_forms'])) {
            echo '<h2>' . __('Top Forms', 'academy-analytics') . '</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>' . __('Form Name', 'academy-analytics') . '</th><th>' . __('Submissions', 'academy-analytics') . '</th></tr></thead>';
            echo '<tbody>';
            foreach ($stats['top_forms'] as $form) {
                echo '<tr>';
                echo '<td>' . esc_html($form->form_name) . '</td>';
                echo '<td>' . number_format($form->count) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        
        // Top pages table
        if (!empty($stats['top_pages'])) {
            echo '<h2>' . __('Top Pages', 'academy-analytics') . '</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>' . __('Page Title', 'academy-analytics') . '</th><th>' . __('URL', 'academy-analytics') . '</th><th>' . __('Visits', 'academy-analytics') . '</th></tr></thead>';
            echo '<tbody>';
            foreach ($stats['top_pages'] as $page) {
                echo '<tr>';
                echo '<td>' . esc_html($page->page_title) . '</td>';
                echo '<td><a href="' . esc_url($page->page_url) . '" target="_blank">' . esc_html($page->page_url) . '</a></td>';
                echo '<td>' . number_format($page->count) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        
        // Output chart data and JavaScript (must be before closing div)
        echo '<script type="text/javascript">';
        echo 'var academyAnalyticsChartData = ' . wp_json_encode($chart_data) . ';';
        echo '</script>';
        
        echo '</div>';
    }
    
    /**
     * Get KPI cards
     */
    private function get_kpi_cards() {
        $cards = get_option('academy_analytics_kpi_cards', array());
        return is_array($cards) ? $cards : array();
    }
    
    /**
     * Save KPI cards
     */
    private function save_kpi_cards($cards) {
        update_option('academy_analytics_kpi_cards', $cards);
    }
    
    /**
     * Calculate date range from timeframe
     * Returns dates in UTC to match database storage
     */
    private function get_date_range_from_timeframe($timeframe) {
        // Use UTC time for database queries (database stores in UTC)
        $date_to = current_time('Y-m-d 23:59:59', true); // true = UTC
        $date_from = '';
        
        // Get WordPress timezone
        $wp_timezone = wp_timezone();
        $utc_timezone = new DateTimeZone('UTC');
        
        switch ($timeframe) {
            case '1':
                // Start of day 1 day ago in WordPress timezone, convert to UTC
                $wp_timestamp = current_time('timestamp') - DAY_IN_SECONDS;
                $wp_datetime = new DateTime('@' . $wp_timestamp, $wp_timezone);
                $wp_datetime->setTime(0, 0, 0);
                $wp_datetime->setTimezone($utc_timezone);
                $date_from = $wp_datetime->format('Y-m-d H:i:s');
                break;
            case '7':
                $wp_timestamp = current_time('timestamp') - (7 * DAY_IN_SECONDS);
                $wp_datetime = new DateTime('@' . $wp_timestamp, $wp_timezone);
                $wp_datetime->setTime(0, 0, 0);
                $wp_datetime->setTimezone($utc_timezone);
                $date_from = $wp_datetime->format('Y-m-d H:i:s');
                break;
            case '14':
                $wp_timestamp = current_time('timestamp') - (14 * DAY_IN_SECONDS);
                $wp_datetime = new DateTime('@' . $wp_timestamp, $wp_timezone);
                $wp_datetime->setTime(0, 0, 0);
                $wp_datetime->setTimezone($utc_timezone);
                $date_from = $wp_datetime->format('Y-m-d H:i:s');
                break;
            case '30':
                $wp_timestamp = current_time('timestamp') - (30 * DAY_IN_SECONDS);
                $wp_datetime = new DateTime('@' . $wp_timestamp, $wp_timezone);
                $wp_datetime->setTime(0, 0, 0);
                $wp_datetime->setTimezone($utc_timezone);
                $date_from = $wp_datetime->format('Y-m-d H:i:s');
                break;
            case '90':
                $wp_timestamp = current_time('timestamp') - (90 * DAY_IN_SECONDS);
                $wp_datetime = new DateTime('@' . $wp_timestamp, $wp_timezone);
                $wp_datetime->setTime(0, 0, 0);
                $wp_datetime->setTimezone($utc_timezone);
                $date_from = $wp_datetime->format('Y-m-d H:i:s');
                break;
            case 'custom':
                if (isset($_GET['date_from'])) {
                    $wp_date_str = sanitize_text_field($_GET['date_from']) . ' 00:00:00';
                    $wp_datetime = new DateTime($wp_date_str, $wp_timezone);
                    $wp_datetime->setTimezone($utc_timezone);
                    $date_from = $wp_datetime->format('Y-m-d H:i:s');
                } else {
                    $wp_timestamp = current_time('timestamp') - (30 * DAY_IN_SECONDS);
                    $wp_datetime = new DateTime('@' . $wp_timestamp, $wp_timezone);
                    $wp_datetime->setTime(0, 0, 0);
                    $wp_datetime->setTimezone($utc_timezone);
                    $date_from = $wp_datetime->format('Y-m-d H:i:s');
                }
                if (isset($_GET['date_to'])) {
                    $wp_date_str = sanitize_text_field($_GET['date_to']) . ' 23:59:59';
                    $wp_datetime = new DateTime($wp_date_str, $wp_timezone);
                    $wp_datetime->setTimezone($utc_timezone);
                    $date_to = $wp_datetime->format('Y-m-d H:i:s');
                } else {
                    $date_to = current_time('Y-m-d 23:59:59', true);
                }
                break;
        }
        
        return array('from' => $date_from, 'to' => $date_to);
    }
    
    /**
     * Get count for a KPI card
     */
    private function get_kpi_card_count($card) {
        $date_range = $this->get_date_range_from_timeframe($card['timeframe']);
        
        $args = array(
            'event_type' => $card['event_type'],
            'date_from' => $date_range['from'],
            'date_to' => $date_range['to']
        );
        
        // Add filters
        if (!empty($card['form_name'])) {
            $args['form_name'] = $card['form_name'];
        }
        if (!empty($card['page_url'])) {
            $args['page_url'] = $card['page_url'];
        }
        if (!empty($card['email'])) {
            $args['email'] = $card['email'];
        }
        
        $result = $this->database->get_events($args);
        return $result['total'];
    }
    
    /**
     * Render KPI Dashboard page
     */
    public function render_dashboard_page() {
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle add/edit card
            if (isset($_POST['save_kpi_card'])) {
                check_admin_referer('academy_analytics_kpi_card', 'academy_analytics_nonce');
                $this->handle_save_kpi_card();
            }
            
            // Handle delete card
            if (isset($_POST['delete_kpi_card'])) {
                check_admin_referer('academy_analytics_kpi_card', 'academy_analytics_nonce');
                $this->handle_delete_kpi_card();
            }
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'view';
        $card_id = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;
        
        echo '<div class="wrap academy-analytics-dashboard">';
        echo '<h1>' . __('KPI Dashboard', 'academy-analytics') . '</h1>';
        
        // Show messages
        if (isset($_GET['message'])) {
            $this->show_kpi_message($_GET['message']);
        }
        
        switch ($action) {
            case 'add':
                $this->render_kpi_card_form();
                break;
            case 'edit':
                $this->render_kpi_card_form($card_id);
                break;
            default:
                $this->render_kpi_dashboard();
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Render KPI dashboard with cards
     */
    private function render_kpi_dashboard() {
        $cards = $this->get_kpi_cards();
        
        echo '<div style="margin-bottom: 20px;">';
        echo '<a href="' . esc_url(add_query_arg(array('action' => 'add'), admin_url('admin.php?page=academy-analytics-dashboard'))) . '" class="button button-primary">' . __('Add KPI Card', 'academy-analytics') . '</a>';
        echo '</div>';
        
        if (empty($cards)) {
            echo '<div class="notice notice-info"><p>' . __('No KPI cards yet. Click "Add KPI Card" to create your first one.', 'academy-analytics') . '</p></div>';
            return;
        }
        
        // Display all KPI cards
        echo '<div class="academy-analytics-kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin: 20px 0;">';
        
        foreach ($cards as $id => $card) {
            $count = $this->get_kpi_card_count($card);
            $timeframe_label = $this->get_timeframe_label($card['timeframe']);
            
            // Build view events URL with filters
            $view_events_params = array(
                'page' => 'academy-analytics',
                'event_type' => $card['event_type']
            );
            
            if (!empty($card['form_name'])) {
                $view_events_params['form_name'] = $card['form_name'];
            }
            
            if (!empty($card['page_url'])) {
                $view_events_params['page_url'] = $card['page_url'];
            }
            
            $view_events_url = add_query_arg($view_events_params, admin_url('admin.php'));
            
            // Add date range based on timeframe
            // Get date range in UTC for calculation, but convert back to WordPress timezone for URL display
            $date_range_utc = $this->get_date_range_from_timeframe($card['timeframe']);
            $wp_timezone = wp_timezone();
            $utc_timezone = new DateTimeZone('UTC');
            
            if (!empty($date_range_utc['from'])) {
                // Convert UTC date back to WordPress timezone for display in URL
                $utc_datetime = new DateTime($date_range_utc['from'], $utc_timezone);
                $utc_datetime->setTimezone($wp_timezone);
                $view_events_url = add_query_arg('date_from', $utc_datetime->format('Y-m-d'), $view_events_url);
            }
            if (!empty($date_range_utc['to'])) {
                // Convert UTC date back to WordPress timezone for display in URL
                $utc_datetime = new DateTime($date_range_utc['to'], $utc_timezone);
                $utc_datetime->setTimezone($wp_timezone);
                $view_events_url = add_query_arg('date_to', $utc_datetime->format('Y-m-d'), $view_events_url);
            }
            
            echo '<div class="kpi-card" style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: relative;">';
            
            // Edit/Delete icons
            echo '<div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 8px;">';
            echo '<a href="' . esc_url(add_query_arg(array('action' => 'edit', 'card_id' => $id))) . '" class="dashicons dashicons-edit" style="text-decoration: none; color: #2271b1; font-size: 20px; width: 20px; height: 20px; display: inline-block;" title="' . esc_attr__('Edit', 'academy-analytics') . '"></a>';
            echo '<form method="post" action="" style="display: inline;" onsubmit="return confirm(\'' . esc_js(__('Delete this KPI card?', 'academy-analytics')) . '\')">';
            wp_nonce_field('academy_analytics_kpi_card', 'academy_analytics_nonce');
            echo '<input type="hidden" name="card_id" value="' . esc_attr($id) . '" />';
            echo '<button type="submit" name="delete_kpi_card" class="dashicons dashicons-trash" style="background: none; border: none; color: #d63638; font-size: 20px; width: 20px; height: 20px; cursor: pointer; padding: 0; margin: 0;" title="' . esc_attr__('Delete', 'academy-analytics') . '"></button>';
            echo '</form>';
            echo '</div>';
            
            // Card content
            echo '<h3 style="margin: 0 0 15px 0; font-size: 16px; color: #1d2327; font-weight: 600; padding-right: 60px;">' . esc_html($card['title']) . '</h3>';
            echo '<div style="font-size: 48px; font-weight: bold; color: #2271b1; margin: 15px 0;">' . number_format($count) . '</div>';
            echo '<p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">';
            echo esc_html(ucwords(str_replace('_', ' ', $card['event_type'])));
            if (!empty($card['form_name'])) {
                echo ': ' . esc_html($card['form_name']);
            }
            if (!empty($card['page_url'])) {
                echo ' - ' . esc_html($card['page_url']);
            }
            echo '</p>';
            echo '<p style="margin: 5px 0 0 0; font-size: 11px; color: #999;">' . esc_html($timeframe_label) . '</p>';
            
            // View Events link
            if ($count > 0) {
                echo '<p style="margin: 15px 0 0 0; padding-top: 10px; border-top: 1px solid #eee;">';
                echo '<a href="' . esc_url($view_events_url) . '" style="text-decoration: none; color: #2271b1; font-size: 13px; display: inline-flex; align-items: center; gap: 5px;">';
                echo '<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px;"></span>';
                echo __('View Events', 'academy-analytics') . ' (' . number_format($count) . ')';
                echo '</a>';
                echo '</p>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Get timeframe label
     */
    private function get_timeframe_label($timeframe) {
        $labels = array(
            '1' => __('Past Day', 'academy-analytics'),
            '7' => __('Past 7 Days', 'academy-analytics'),
            '14' => __('Past 14 Days', 'academy-analytics'),
            '30' => __('Past 30 Days', 'academy-analytics'),
            '90' => __('Past 90 Days', 'academy-analytics'),
            'custom' => __('Custom Range', 'academy-analytics')
        );
        return isset($labels[$timeframe]) ? $labels[$timeframe] : $timeframe;
    }
    
    /**
     * Render KPI card form
     */
    private function render_kpi_card_form($card_id = 0) {
        $cards = $this->get_kpi_cards();
        $card = $card_id && isset($cards[$card_id]) ? $cards[$card_id] : array();
        $is_edit = !empty($card);
        
        // Get all form names and page URLs for autocomplete
        global $wpdb;
        $forms_query = "SELECT DISTINCT form_name FROM {$this->database->get_table_name()} WHERE form_name IS NOT NULL AND form_name != '' ORDER BY form_name ASC";
        $all_forms = $wpdb->get_col($forms_query);
        
        $pages_query = "SELECT DISTINCT page_url FROM {$this->database->get_table_name()} WHERE page_url IS NOT NULL AND page_url != '' ORDER BY page_url ASC LIMIT 100";
        $all_pages = $wpdb->get_col($pages_query);
        
        echo '<h2>' . ($is_edit ? __('Edit KPI Card', 'academy-analytics') : __('Add KPI Card', 'academy-analytics')) . '</h2>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=academy-analytics-dashboard')) . '" class="button" style="margin-bottom: 20px;">' . __('Back to Dashboard', 'academy-analytics') . '</a>';
        
        echo '<form method="post" action="">';
        wp_nonce_field('academy_analytics_kpi_card', 'academy_analytics_nonce');
        
        if ($is_edit) {
            echo '<input type="hidden" name="card_id" value="' . esc_attr($card_id) . '" />';
        }
        
        echo '<table class="form-table">';
        
        echo '<tr>';
        echo '<th scope="row"><label for="card_title">' . __('Card Title', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="card_title" id="card_title" value="' . esc_attr($is_edit ? $card['title'] : '') . '" class="regular-text" required />';
        echo '<p class="description">' . __('e.g., "Site Logins", "Starter Program Submissions"', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="card_event_type">' . __('Event Type', 'academy-analytics') . '</label></th>';
        echo '<td>';
        $event_types = academy_analytics()->get_event_types();
        echo '<select name="card_event_type" id="card_event_type" required>';
        echo '<option value="">' . __('Select...', 'academy-analytics') . '</option>';
        foreach ($event_types as $type_id => $type_data) {
            echo '<option value="' . esc_attr($type_id) . '"' . selected($is_edit ? $card['event_type'] : '', $type_id, false) . '>' . esc_html($type_data['label']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="card_form_name">' . __('Form Name (optional)', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="card_form_name" id="card_form_name" value="' . esc_attr($is_edit && isset($card['form_name']) ? $card['form_name'] : '') . '" list="form_names_list" class="regular-text" />';
        echo '<datalist id="form_names_list">';
        foreach ($all_forms as $form) {
            echo '<option value="' . esc_attr($form) . '">';
        }
        echo '</datalist>';
        echo '<p class="description">' . __('Filter by specific form name (e.g., "starter_free")', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="card_page_url">' . __('Page URL (optional)', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="card_page_url" id="card_page_url" value="' . esc_attr($is_edit && isset($card['page_url']) ? $card['page_url'] : '') . '" list="page_urls_list" class="regular-text" />';
        echo '<datalist id="page_urls_list">';
        foreach ($all_pages as $page) {
            echo '<option value="' . esc_attr($page) . '">';
        }
        echo '</datalist>';
        echo '<p class="description">' . __('Filter by specific page URL', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="card_timeframe">' . __('Time Frame', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<select name="card_timeframe" id="card_timeframe" required>';
        echo '<option value="1"' . selected($is_edit && isset($card['timeframe']) ? $card['timeframe'] : '30', '1', false) . '>' . __('Past Day', 'academy-analytics') . '</option>';
        echo '<option value="7"' . selected($is_edit && isset($card['timeframe']) ? $card['timeframe'] : '30', '7', false) . '>' . __('Past 7 Days', 'academy-analytics') . '</option>';
        echo '<option value="14"' . selected($is_edit && isset($card['timeframe']) ? $card['timeframe'] : '30', '14', false) . '>' . __('Past 14 Days', 'academy-analytics') . '</option>';
        echo '<option value="30"' . selected($is_edit && isset($card['timeframe']) ? $card['timeframe'] : '30', '30', false) . '>' . __('Past 30 Days', 'academy-analytics') . '</option>';
        echo '<option value="90"' . selected($is_edit && isset($card['timeframe']) ? $card['timeframe'] : '30', '90', false) . '>' . __('Past 90 Days', 'academy-analytics') . '</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="save_kpi_card" class="button button-primary" value="' . esc_attr($is_edit ? __('Update Card', 'academy-analytics') : __('Add Card', 'academy-analytics')) . '" />';
        echo ' <a href="' . esc_url(admin_url('admin.php?page=academy-analytics-dashboard')) . '" class="button">' . __('Cancel', 'academy-analytics') . '</a>';
        echo '</p>';
        
        echo '</form>';
    }
    
    /**
     * Handle save KPI card
     */
    private function handle_save_kpi_card() {
        $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        $cards = $this->get_kpi_cards();
        
        $card = array(
            'title' => sanitize_text_field($_POST['card_title']),
            'event_type' => sanitize_text_field($_POST['card_event_type']),
            'form_name' => !empty($_POST['card_form_name']) ? sanitize_text_field($_POST['card_form_name']) : '',
            'page_url' => !empty($_POST['card_page_url']) ? esc_url_raw($_POST['card_page_url']) : '',
            'timeframe' => sanitize_text_field($_POST['card_timeframe'])
        );
        
        if ($card_id && isset($cards[$card_id])) {
            // Update existing
            $cards[$card_id] = $card;
            $message = 'updated';
        } else {
            // Add new
            $new_id = !empty($cards) ? max(array_keys($cards)) + 1 : 1;
            $cards[$new_id] = $card;
            $message = 'created';
        }
        
        $this->save_kpi_cards($cards);
        
        wp_redirect(add_query_arg('message', $message, admin_url('admin.php?page=academy-analytics-dashboard')));
        exit;
    }
    
    /**
     * Handle delete KPI card
     */
    private function handle_delete_kpi_card() {
        $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        
        if ($card_id) {
            $cards = $this->get_kpi_cards();
            unset($cards[$card_id]);
            $this->save_kpi_cards($cards);
        }
        
        wp_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=academy-analytics-dashboard')));
        exit;
    }
    
    /**
     * Show KPI message
     */
    private function show_kpi_message($message) {
        $class = 'notice-success';
        $text = '';
        
        switch ($message) {
            case 'created':
                $text = __('KPI card created successfully.', 'academy-analytics');
                break;
            case 'updated':
                $text = __('KPI card updated successfully.', 'academy-analytics');
                break;
            case 'deleted':
                $text = __('KPI card deleted successfully.', 'academy-analytics');
                break;
        }
        
        if ($text) {
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_settings'])) {
                check_admin_referer('academy_analytics_settings', 'academy_analytics_nonce');
                
                if (isset($_POST['webhook_secret'])) {
                    update_option('academy_analytics_webhook_secret', sanitize_text_field($_POST['webhook_secret']));
                }
                
                if (isset($_POST['regenerate_secret'])) {
                    $new_secret = wp_generate_password(32, false);
                    update_option('academy_analytics_webhook_secret', $new_secret);
                }
                
                // Save throttle setting
                if (isset($_POST['throttle_seconds'])) {
                    $throttle = intval($_POST['throttle_seconds']);
                    if ($throttle >= 0) {
                        update_option('academy_analytics_throttle_seconds', $throttle);
                    }
                }
                
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved.', 'academy-analytics') . '</p></div>';
            }
            
            // Handle create tables
            if (isset($_POST['create_tables'])) {
                check_admin_referer('academy_analytics_create_tables', 'academy_analytics_nonce');
                $database = new Academy_Analytics_Database();
                $database->create_tables();
                
                // Check if table was created
                global $wpdb;
                $table_name = $database->get_table_name();
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
                
                if ($table_exists) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Database table created successfully!', 'academy-analytics') . '</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to create database table. Please check your database permissions.', 'academy-analytics') . '</p></div>';
                }
            }
            
            // Handle event type management
            if (isset($_POST['add_event_type'])) {
                check_admin_referer('academy_analytics_event_types', 'academy_analytics_nonce');
                $this->handle_add_event_type();
            }
            
            if (isset($_POST['delete_event_type'])) {
                check_admin_referer('academy_analytics_event_types', 'academy_analytics_nonce');
                $this->handle_delete_event_type();
            }
        }
        
        $webhook_url = academy_analytics()->get_webhook_url();
        $webhook_secret = academy_analytics()->get_webhook_secret();
        $throttle_seconds = get_option('academy_analytics_throttle_seconds', 60);
        $event_types = academy_analytics()->get_event_types();
        
        echo '<div class="wrap">';
        echo '<h1>' . __('Academy Analytics Settings', 'academy-analytics') . '</h1>';
        
        // Webhook Settings
        echo '<h2>' . __('Webhook Configuration', 'academy-analytics') . '</h2>';
        echo '<form method="post" action="">';
        wp_nonce_field('academy_analytics_settings', 'academy_analytics_nonce');
        
        echo '<table class="form-table">';
        
        echo '<tr>';
        echo '<th scope="row"><label for="webhook_url">' . __('Webhook URL', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="text" id="webhook_url" value="' . esc_attr($webhook_url) . '" readonly style="width: 100%; font-family: monospace;" />';
        echo '<button type="button" class="button copy-webhook-url" data-url="' . esc_attr($webhook_url) . '" style="margin-left: 10px;">' . __('Copy URL', 'academy-analytics') . '</button>';
        echo '<p class="description">' . __('Use this URL in Flowmattic to send events to this plugin.', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="webhook_secret">' . __('Webhook Secret', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="webhook_secret" id="webhook_secret" value="' . esc_attr($webhook_secret) . '" style="width: 100%; font-family: monospace;" />';
        echo '<p class="description">' . __('Optional: Set a secret key for webhook authentication. Include it in the X-Webhook-Secret header or as a "secret" parameter in the request body.', 'academy-analytics') . '</p>';
        echo '<label><input type="checkbox" name="regenerate_secret" value="1" /> ' . __('Regenerate secret key', 'academy-analytics') . '</label>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="throttle_seconds">' . __('Throttle Period', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="number" name="throttle_seconds" id="throttle_seconds" value="' . esc_attr($throttle_seconds) . '" min="0" step="1" style="width: 100px;" />';
        echo ' <span>' . __('seconds', 'academy-analytics') . '</span>';
        echo '<p class="description">' . __('Prevent duplicate events from being recorded within this time period. Events with the same event_type and email/user_id will be ignored if a similar event was recorded within this timeframe. Set to 0 to disable throttling.', 'academy-analytics') . '</p>';
        echo '<p class="description"><strong>' . __('Example:', 'academy-analytics') . '</strong> ' . __('If set to 60 seconds, a login event for the same user will only be recorded once per minute, even if multiple login events are triggered.', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="save_settings" class="button button-primary" value="' . __('Save Settings', 'academy-analytics') . '" />';
        echo '</p>';
        
        echo '</form>';
        
        // Database table status and creation
        global $wpdb;
        $database = new Academy_Analytics_Database();
        $table_name = $database->get_table_name();
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        
        echo '<hr style="margin: 30px 0;" />';
        echo '<h2>' . __('Database Status', 'academy-analytics') . '</h2>';
        
        if ($table_exists) {
            $row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
            echo '<div class="notice notice-success inline"><p>';
            echo '<strong>' . __('Table Status:', 'academy-analytics') . '</strong> ' . __('Table exists', 'academy-analytics') . ' (' . number_format($row_count) . ' ' . __('events', 'academy-analytics') . ')';
            echo '</p></div>';
        } else {
            echo '<div class="notice notice-error inline"><p>';
            echo '<strong>' . __('Table Status:', 'academy-analytics') . '</strong> ' . __('Table does not exist. Click the button below to create it.', 'academy-analytics');
            echo '</p></div>';
            
            echo '<form method="post" action="" style="margin-top: 15px;">';
            wp_nonce_field('academy_analytics_create_tables', 'academy_analytics_nonce');
            echo '<input type="submit" name="create_tables" class="button button-primary" value="' . __('Create Database Table', 'academy-analytics') . '" />';
            echo '</form>';
        }
        
        // Event Types Management
        echo '<h2>' . __('Event Types', 'academy-analytics') . '</h2>';
        echo '<p class="description">' . __('Manage available event types. You can use any event type in your webhook payload, but defining them here helps with organization and filtering.', 'academy-analytics') . '</p>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">' . __('Event Type ID', 'academy-analytics') . '</th>';
        echo '<th scope="col">' . __('Label', 'academy-analytics') . '</th>';
        echo '<th scope="col">' . __('Description', 'academy-analytics') . '</th>';
        echo '<th scope="col">' . __('Actions', 'academy-analytics') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($event_types)) {
            echo '<tr><td colspan="4">' . __('No event types defined.', 'academy-analytics') . '</td></tr>';
        } else {
            foreach ($event_types as $type_id => $type_data) {
                echo '<tr>';
                echo '<td><code>' . esc_html($type_id) . '</code></td>';
                echo '<td>' . esc_html($type_data['label']) . '</td>';
                echo '<td>' . esc_html($type_data['description']) . '</td>';
                echo '<td>';
                echo '<form method="post" action="" style="display: inline;">';
                wp_nonce_field('academy_analytics_event_types', 'academy_analytics_nonce');
                echo '<input type="hidden" name="event_type_id" value="' . esc_attr($type_id) . '" />';
                echo '<input type="submit" name="delete_event_type" class="button button-link-delete" value="' . __('Delete', 'academy-analytics') . '" onclick="return confirm(\'' . esc_js(__('Are you sure?', 'academy-analytics')) . '\')" />';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // Add new event type form
        echo '<h3>' . __('Add New Event Type', 'academy-analytics') . '</h3>';
        echo '<form method="post" action="">';
        wp_nonce_field('academy_analytics_event_types', 'academy_analytics_nonce');
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="new_event_type_id">' . __('Event Type ID', 'academy-analytics') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="event_type_id" id="new_event_type_id" class="regular-text" required pattern="[a-z0-9_]+" />';
        echo '<p class="description">' . __('Lowercase letters, numbers, and underscores only (e.g., "form_submission", "page_visit", "button_click")', 'academy-analytics') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="event_type_label">' . __('Label', 'academy-analytics') . '</label></th>';
        echo '<td><input type="text" name="event_type_label" id="event_type_label" class="regular-text" required /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="event_type_description">' . __('Description', 'academy-analytics') . '</label></th>';
        echo '<td><textarea name="event_type_description" id="event_type_description" class="large-text" rows="3"></textarea></td>';
        echo '</tr>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="add_event_type" class="button button-primary" value="' . __('Add Event Type', 'academy-analytics') . '" />';
        echo '</p>';
        echo '</form>';
        
        // Integration methods documentation
        echo '<h2>' . __('Integration Methods', 'academy-analytics') . '</h2>';
        echo '<div class="card" style="max-width: 1000px;">';
        
        echo '<h3>' . __('Method 1: Direct PHP Function (Recommended - Faster)', 'academy-analytics') . '</h3>';
        echo '<p>' . __('If Flowmattic can call PHP functions directly, use this method. It\'s faster because it bypasses HTTP/REST API.', 'academy-analytics') . '</p>';
        echo '<p><strong>' . __('Function Name:', 'academy-analytics') . '</strong> <code>academy_analytics_record_event()</code></p>';
        
        echo '<h4>' . __('Flowmattic Setup:', 'academy-analytics') . '</h4>';
        echo '<ol>';
        echo '<li>' . __('In Flowmattic, add a "PHP Function" action', 'academy-analytics') . '</li>';
        echo '<li>' . __('Function name: <code>academy_analytics_record_event</code>', 'academy-analytics') . '</li>';
        echo '<li>' . __('Pass your data as an array parameter', 'academy-analytics') . '</li>';
        echo '</ol>';
        
        echo '<h4>' . __('Example PHP Code for Flowmattic:', 'academy-analytics') . '</h4>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; border-left: 4px solid #00a32a;">';
        echo esc_html('<?php
// Form submission example
$result = academy_analytics_record_event(array(
    \'event_type\' => \'form_submission\',
    \'email\' => $email, // from Flowmattic variable
    \'form_name\' => \'Contact Form\',
    \'data\' => array(
        \'name\' => $name,
        \'phone\' => $phone,
        \'message\' => $message
    )
));

// Check result
if (is_wp_error($result)) {
    // Handle error
    error_log(\'Analytics error: \' . $result->get_error_message());
} else {
    // Success - event_id is in $result[\'event_id\']
}
?>');
        echo '</pre>';
        
        echo '<h4>' . __('Return Value:', 'academy-analytics') . '</h4>';
        echo '<p>' . __('On success, returns an array with <code>success</code>, <code>event_id</code>, and <code>message</code>. On error, returns a <code>WP_Error</code> object.', 'academy-analytics') . '</p>';
        
        echo '<hr style="margin: 30px 0;" />';
        
        echo '<h3>' . __('Method 2: Webhook (HTTP/REST API)', 'academy-analytics') . '</h3>';
        echo '<p>' . __('Use this method when Flowmattic cannot call PHP functions directly, or when calling from external services.', 'academy-analytics') . '</p>';
        echo '<p><strong>' . __('Webhook URL:', 'academy-analytics') . '</strong> <code>' . esc_html($webhook_url) . '</code></p>';
        
        echo '<h4>' . __('Flowmattic Setup:', 'academy-analytics') . '</h4>';
        echo '<ol>';
        echo '<li>' . __('In Flowmattic, add a "Webhook" action', 'academy-analytics') . '</li>';
        echo '<li>' . __('Set method to <strong>POST</strong>', 'academy-analytics') . '</li>';
        echo '<li>' . __('Enter the webhook URL above', 'academy-analytics') . '</li>';
        echo '<li>' . __('Configure the payload with your data', 'academy-analytics') . '</li>';
        echo '</ol>';
        
        echo '<h4>' . __('Example Webhook Payload:', 'academy-analytics') . '</h4>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; border-left: 4px solid #2271b1;">';
        echo esc_html(json_encode(array(
            'event_type' => 'form_submission',
            'email' => 'user@example.com',
            'form_name' => 'Contact Form',
            'data' => array(
                'name' => 'John Doe',
                'message' => 'Hello world'
            )
        ), JSON_PRETTY_PRINT));
        echo '</pre>';
        
        echo '<hr style="margin: 30px 0;" />';
        
        echo '<h3>' . __('Comparison', 'academy-analytics') . '</h3>';
        echo '<table class="widefat" style="margin-top: 10px;">';
        echo '<thead><tr><th>' . __('Method', 'academy-analytics') . '</th><th>' . __('Speed', 'academy-analytics') . '</th><th>' . __('Use Case', 'academy-analytics') . '</th></tr></thead>';
        echo '<tbody>';
        echo '<tr><td><strong>PHP Function</strong></td><td>' . __('Faster (direct call)', 'academy-analytics') . '</td><td>' . __('Flowmattic on same server', 'academy-analytics') . '</td></tr>';
        echo '<tr><td><strong>Webhook</strong></td><td>' . __('Slower (HTTP request)', 'academy-analytics') . '</td><td>' . __('External services or when PHP functions not available', 'academy-analytics') . '</td></tr>';
        echo '</tbody>';
        echo '</table>';
        
        echo '<hr style="margin: 30px 0;" />';
        
        echo '<h3>' . __('Available Event Types', 'academy-analytics') . '</h3>';
        
        echo '<h3>' . __('Available Event Types', 'academy-analytics') . '</h3>';
        if (!empty($event_types)) {
            echo '<ul>';
            foreach ($event_types as $type_id => $type_data) {
                echo '<li><strong><code>' . esc_html($type_id) . '</code></strong> - ' . esc_html($type_data['label']);
                if (!empty($type_data['description'])) {
                    echo ' (' . esc_html($type_data['description']) . ')';
                }
                echo '</li>';
            }
            echo '</ul>';
            echo '<p><em>' . __('Note: You can use any event type in your webhook payload, even if it\'s not listed above. These are just suggestions for organization.', 'academy-analytics') . '</em></p>';
        } else {
            echo '<p>' . __('No event types defined yet. Add some above!', 'academy-analytics') . '</p>';
        }
        
        echo '<h3>' . __('Example Payloads', 'academy-analytics') . '</h3>';
        
        echo '<h4>' . __('Form Submission', 'academy-analytics') . '</h4>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; border-left: 4px solid #2271b1;">';
        echo esc_html(json_encode(array(
            'event_type' => 'form_submission',
            'email' => 'user@example.com',
            'form_name' => 'Contact Form',
            'user_id' => 123,
            'data' => array(
                'name' => 'John Doe',
                'phone' => '555-1234',
                'message' => 'Hello world',
                'custom_field' => 'value'
            )
        ), JSON_PRETTY_PRINT));
        echo '</pre>';
        
        echo '<h4>' . __('Page Visit', 'academy-analytics') . '</h4>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; border-left: 4px solid #00a32a;">';
        echo esc_html(json_encode(array(
            'event_type' => 'page_visit',
            'page_title' => 'Jazz Piano Lessons',
            'page_url' => 'https://yoursite.com/lessons/jazz-piano',
            'referrer' => 'https://google.com',
            'email' => 'user@example.com',
            'ip_address' => '192.168.1.1',
            'data' => array(
                'session_id' => 'xyz789',
                'device' => 'desktop',
                'browser' => 'Chrome',
                'duration_seconds' => 45
            )
        ), JSON_PRETTY_PRINT));
        echo '</pre>';
        
        echo '<h4>' . __('Custom Event Type', 'academy-analytics') . '</h4>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; border-left: 4px solid #646970;">';
        echo esc_html(json_encode(array(
            'event_type' => 'button_click',
            'email' => 'user@example.com',
            'data' => array(
                'button_id' => 'cta-signup',
                'button_text' => 'Sign Up Now',
                'page_url' => 'https://yoursite.com/pricing',
                'timestamp' => '2024-01-15 10:30:00'
            )
        ), JSON_PRETTY_PRINT));
        echo '</pre>';
        
        echo '<h3>' . __('Required Fields', 'academy-analytics') . '</h3>';
        echo '<ul>';
        echo '<li><strong><code>event_type</code></strong> - ' . __('The type of event (required). Can be any string, but we recommend using lowercase with underscores.', 'academy-analytics') . '</li>';
        echo '</ul>';
        
        echo '<h3>' . __('Optional Standard Fields', 'academy-analytics') . '</h3>';
        echo '<ul>';
        echo '<li><strong><code>user_id</code></strong> - ' . __('WordPress user ID', 'academy-analytics') . '</li>';
        echo '<li><strong><code>email</code></strong> - ' . __('User email address', 'academy-analytics') . '</li>';
        echo '<li><strong><code>form_name</code></strong> - ' . __('Name/ID of the form (for form submissions)', 'academy-analytics') . '</li>';
        echo '<li><strong><code>page_title</code></strong> - ' . __('Title of the page (for page visits)', 'academy-analytics') . '</li>';
        echo '<li><strong><code>page_url</code></strong> - ' . __('Full URL of the page', 'academy-analytics') . '</li>';
        echo '<li><strong><code>referrer</code></strong> - ' . __('HTTP referrer URL', 'academy-analytics') . '</li>';
        echo '<li><strong><code>ip_address</code></strong> - ' . __('Client IP address (auto-detected if not provided)', 'academy-analytics') . '</li>';
        echo '</ul>';
        
        echo '<h3>' . __('Custom Data', 'academy-analytics') . '</h3>';
        echo '<p>' . __('Any additional fields you include in the payload will be stored in the <code>data</code> JSON column. This allows you to store any custom information without modifying the database schema.', 'academy-analytics') . '</p>';
        
        echo '<h3>' . __('Authentication', 'academy-analytics') . '</h3>';
        echo '<p>' . __('If you set a webhook secret above, include it in your requests:', 'academy-analytics') . '</p>';
        echo '<ul>';
        echo '<li><strong>Header:</strong> <code>X-Webhook-Secret: your-secret-key</code></li>';
        echo '<li><strong>Query Parameter:</strong> <code>?secret=your-secret-key</code></li>';
        echo '<li><strong>Body Parameter:</strong> <code>{"secret": "your-secret-key", ...}</code></li>';
        echo '</ul>';
        
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Handle add event type
     */
    private function handle_add_event_type() {
        $type_id = isset($_POST['event_type_id']) ? sanitize_key($_POST['event_type_id']) : '';
        $label = isset($_POST['event_type_label']) ? sanitize_text_field($_POST['event_type_label']) : '';
        $description = isset($_POST['event_type_description']) ? sanitize_text_field($_POST['event_type_description']) : '';
        
        if (empty($type_id) || empty($label)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Event type ID and label are required.', 'academy-analytics') . '</p></div>';
            return;
        }
        
        $event_types = academy_analytics()->get_event_types();
        $event_types[$type_id] = array(
            'label' => $label,
            'description' => $description
        );
        
        update_option('academy_analytics_event_types', $event_types);
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Event type added successfully.', 'academy-analytics') . '</p></div>';
    }
    
    /**
     * Handle delete event type
     */
    private function handle_delete_event_type() {
        $type_id = isset($_POST['event_type_id']) ? sanitize_key($_POST['event_type_id']) : '';
        
        if (empty($type_id)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Event type ID is required.', 'academy-analytics') . '</p></div>';
            return;
        }
        
        $event_types = academy_analytics()->get_event_types();
        unset($event_types[$type_id]);
        
        update_option('academy_analytics_event_types', $event_types);
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Event type deleted successfully.', 'academy-analytics') . '</p></div>';
    }
}

