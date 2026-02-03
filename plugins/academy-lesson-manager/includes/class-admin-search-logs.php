<?php
/**
 * Search Logs Admin Class
 * 
 * Handles the search logs admin page functionality
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Search_Logs {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->table_name = $this->database->get_table_name('search_logs');
    }
    
    /**
     * Render the search logs admin page
     */
    public function render_page() {
        // Check if table exists, create if not
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name;
        if (!$table_exists) {
            // Try to create the table
            $this->database->create_tables();
            $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name;
            
            if ($table_exists) {
                echo '<div class="notice notice-success is-dismissible"><p>Search logs table created successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Error: Could not create search logs table. Please check database permissions.</p></div>';
            }
        }
        
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $this->handle_delete(intval($_GET['id']));
        }
        
        // Handle bulk delete
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['log_ids'])) {
            $this->handle_bulk_delete($_POST['log_ids']);
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . __('Search Logs', 'academy-lesson-manager') . '</h1>';
        
        if (!$table_exists) {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> The search logs table does not exist. Please deactivate and reactivate the plugin, or contact support.</p></div>';
        } else {
            $this->render_list_page();
        }
        
        echo '</div>';
    }
    
    /**
     * Render the list page
     */
    private function render_list_page() {
        // Get filter parameters
        $search_query_filter = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';
        $source_filter = isset($_GET['source']) ? sanitize_text_field($_GET['source']) : '';
        $user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50;
        $offset = ($paged - 1) * $per_page;
        
        // Build WHERE clause
        $where = array('1=1');
        $params = array();
        
        if (!empty($search_query_filter)) {
            $where[] = "search_query LIKE %s";
            $params[] = '%' . $this->wpdb->esc_like($search_query_filter) . '%';
        }
        
        if (!empty($source_filter) && in_array($source_filter, array('dashboard', 'shortcode'), true)) {
            $where[] = "search_source = %s";
            $params[] = $source_filter;
        }
        
        if ($user_filter > 0) {
            $where[] = "user_id = %d";
            $params[] = $user_filter;
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Get total count
        if (!empty($params)) {
            $count_sql = $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}", $params);
        } else {
            $count_sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}";
        }
        $total_items = intval($this->wpdb->get_var($count_sql));
        $total_pages = ceil($total_items / $per_page);
        
        // Get logs
        $order_by = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        $allowed_orderby = array('ID', 'user_id', 'search_query', 'search_source', 'results_count', 'created_at');
        if (!in_array($order_by, $allowed_orderby)) {
            $order_by = 'created_at';
        }
        
        // Build SQL with LIMIT/OFFSET placeholders
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_sql} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d";
        
        // Add LIMIT and OFFSET to params array (always at the end)
        $all_params = $params;
        $all_params[] = $per_page;
        $all_params[] = $offset;
        
        // Always prepare the query since we always have LIMIT/OFFSET placeholders
        $logs = $this->wpdb->get_results($this->wpdb->prepare($sql, $all_params), ARRAY_A);
        
        // Render filters
        $this->render_filters($search_query_filter, $source_filter, $user_filter);
        
        // Render table
        echo '<form method="post" action="">';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="check-column"><input type="checkbox" id="cb-select-all" /></th>';
        echo '<th>' . $this->get_sortable_header('ID', 'ID', $order_by, $order) . '</th>';
        echo '<th>' . $this->get_sortable_header('User ID', 'user_id', $order_by, $order) . '</th>';
        echo '<th>User Name</th>';
        echo '<th>Email</th>';
        echo '<th>' . $this->get_sortable_header('Search Query', 'search_query', $order_by, $order) . '</th>';
        echo '<th>' . $this->get_sortable_header('Source', 'search_source', $order_by, $order) . '</th>';
        echo '<th>' . $this->get_sortable_header('Results', 'results_count', $order_by, $order) . '</th>';
        echo '<th>' . $this->get_sortable_header('Date', 'created_at', $order_by, $order) . '</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($logs)) {
            echo '<tr><td colspan="10">' . __('No search logs found.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($logs as $log) {
                $user_id = intval($log['user_id']);
                $user = $user_id > 0 ? get_user_by('id', $user_id) : null;
                
                // Build user display with link to user edit page
                if ($user) {
                    $user_edit_url = admin_url('user-edit.php?user_id=' . $user_id);
                    $user_display = '<a href="' . esc_url($user_edit_url) . '">' . esc_html($user->display_name) . '</a>';
                    $user_email = '<a href="' . esc_url($user_edit_url) . '">' . esc_html($user->user_email) . '</a>';
                    $user_id_display = '<a href="' . esc_url($user_edit_url) . '">' . esc_html($user_id) . '</a>';
                } else {
                    $user_display = '<em>Guest</em>';
                    $user_email = '<em>—</em>';
                    $user_id_display = '<em>0</em>';
                }
                
                echo '<tr>';
                echo '<th scope="row" class="check-column"><input type="checkbox" name="log_ids[]" value="' . esc_attr($log['ID']) . '" /></th>';
                echo '<td>' . esc_html($log['ID']) . '</td>';
                echo '<td>' . $user_id_display . '</td>';
                echo '<td>' . $user_display . '</td>';
                echo '<td>' . $user_email . '</td>';
                echo '<td><strong>' . esc_html($log['search_query']) . '</strong></td>';
                echo '<td><span class="dashicons dashicons-' . ($log['search_source'] === 'dashboard' ? 'dashboard' : 'shortcode') . '"></span> ' . esc_html(ucfirst($log['search_source'])) . '</td>';
                echo '<td>' . esc_html($log['results_count']) . '</td>';
                echo '<td>' . esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $log['created_at'])) . '</td>';
                echo '<td><a href="' . esc_url(add_query_arg(array('action' => 'delete', 'id' => $log['ID']))) . '" class="button-link-delete" onclick="return confirm(\'Are you sure you want to delete this log entry?\');">Delete</a></td>';
                echo '</tr>';
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // Bulk actions
        echo '<div class="tablenav bottom">';
        echo '<div class="alignleft actions bulkactions">';
        echo '<select name="action">';
        echo '<option value="">Bulk Actions</option>';
        echo '<option value="delete">Delete</option>';
        echo '</select>';
        echo '<input type="submit" class="button action" value="Apply">';
        echo '</div>';
        
        // Pagination
        if ($total_pages > 1) {
            echo '<div class="tablenav-pages">';
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $paged
            ));
            echo '</div>';
        }
        
        echo '</div>';
        echo '</form>';
    }
    
    /**
     * Render filters
     */
    private function render_filters($search_query_filter, $source_filter, $user_filter) {
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions">';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="academy-manager-search-logs" />';
        echo '<input type="text" name="search_query" placeholder="Search query..." value="' . esc_attr($search_query_filter) . '" />';
        echo '<select name="source">';
        echo '<option value="">All Sources</option>';
        echo '<option value="dashboard"' . selected($source_filter, 'dashboard', false) . '>Dashboard</option>';
        echo '<option value="shortcode"' . selected($source_filter, 'shortcode', false) . '>Shortcode</option>';
        echo '</select>';
        echo '<input type="number" name="user_id" placeholder="User ID" value="' . esc_attr($user_filter) . '" />';
        echo '<input type="submit" class="button" value="Filter" />';
        if ($search_query_filter || $source_filter || $user_filter) {
            echo '<a href="' . esc_url(admin_url('admin.php?page=academy-manager-search-logs')) . '" class="button">Clear</a>';
        }
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Get sortable header
     */
    private function get_sortable_header($label, $column, $current_orderby, $current_order) {
        $url = add_query_arg(array(
            'orderby' => $column,
            'order' => ($current_orderby === $column && $current_order === 'ASC') ? 'DESC' : 'ASC'
        ));
        
        $class = '';
        if ($current_orderby === $column) {
            $class = 'sorted ' . strtolower($current_order);
        } else {
            $class = 'sortable';
        }
        
        return '<a href="' . esc_url($url) . '" class="' . $class . '"><span>' . esc_html($label) . '</span><span class="sorting-indicator"></span></a>';
    }
    
    /**
     * Handle delete
     */
    private function handle_delete($id) {
        $this->wpdb->delete($this->table_name, array('ID' => $id), array('%d'));
        echo '<div class="notice notice-success is-dismissible"><p>Search log deleted successfully.</p></div>';
    }
    
    /**
     * Handle bulk delete
     */
    private function handle_bulk_delete($ids) {
        if (!is_array($ids)) {
            return;
        }
        
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $this->wpdb->query($this->wpdb->prepare("DELETE FROM {$this->table_name} WHERE ID IN ($placeholders)", $ids));
        echo '<div class="notice notice-success is-dismissible"><p>' . count($ids) . ' search log(s) deleted successfully.</p></div>';
    }
}

