<?php
/**
 * Admin: Essentials selection credit audit log
 *
 * @package Academy_Lesson_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Essentials_Selection_Log {

    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $table_name;

    /**
     * @var ALM_Database
     */
    private $database;

    public function __construct() {
        global $wpdb;
        $this->wpdb     = $wpdb;
        $this->database = new ALM_Database();
        $this->table_name = $this->database->get_table_name('essentials_selection_audit');
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view this page.', 'academy-lesson-manager'));
        }

        $table_ok = $this->ensure_table();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Essentials Selection Log', 'academy-lesson-manager') . '</h1>';
        echo '<p class="description">' . esc_html__('Audit trail for Essentials selection credits: automatic grants, admin actions, and credits used when students add lessons to their library.', 'academy-lesson-manager') . '</p>';

        if (!$table_ok) {
            echo '<div class="notice notice-error"><p>' . esc_html__('The audit table could not be created. Check database permissions and try saving plugin settings or bumping the plugin version.', 'academy-lesson-manager') . '</p></div>';
            echo '</div>';
            return;
        }

        $this->render_filters();
        $this->render_table();

        echo '</div>';
    }

    /**
     * @return bool
     */
    private function ensure_table() {
        $exists = $this->wpdb->get_var($this->wpdb->prepare('SHOW TABLES LIKE %s', $this->table_name)) === $this->table_name;
        if ($exists) {
            return true;
        }
        $this->database->create_tables();
        return $this->wpdb->get_var($this->wpdb->prepare('SHOW TABLES LIKE %s', $this->table_name)) === $this->table_name;
    }

    private function render_filters() {
        $user_id     = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;
        $event_type  = isset($_GET['event_type']) ? sanitize_key(wp_unslash($_GET['event_type'])) : '';
        $labels      = ALM_Essentials_Selection_Audit::event_type_labels();

        $base_url = admin_url('admin.php?page=academy-manager-essentials-selection-log');
        echo '<form method="get" action="' . esc_url(admin_url('admin.php')) . '" style="margin: 1em 0; padding: 12px; background: #fff; border: 1px solid #ccd0d4; max-width: 720px;">';
        echo '<input type="hidden" name="page" value="academy-manager-essentials-selection-log" />';

        echo '<p style="margin: 0 0 10px 0;"><label for="alm-audit-user-id"><strong>' . esc_html__('Student user ID', 'academy-lesson-manager') . '</strong></label><br />';
        echo '<input type="number" min="1" step="1" id="alm-audit-user-id" name="user_id" value="' . ($user_id ? esc_attr((string) $user_id) : '') . '" style="width: 120px;" /> ';
        echo '<span class="description">' . esc_html__('Leave empty to show all students.', 'academy-lesson-manager') . '</span></p>';

        echo '<p style="margin: 0 0 10px 0;"><label for="alm-audit-event"><strong>' . esc_html__('Event type', 'academy-lesson-manager') . '</strong></label><br />';
        echo '<select name="event_type" id="alm-audit-event" style="min-width: 280px;">';
        echo '<option value="">' . esc_html__('All events', 'academy-lesson-manager') . '</option>';
        foreach ($labels as $slug => $text) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($event_type, $slug, false) . '>' . esc_html($text) . '</option>';
        }
        echo '</select></p>';

        submit_button(__('Filter', 'academy-lesson-manager'), 'secondary', 'submit', false);

        if ($user_id || $event_type) {
            echo ' <a class="button" href="' . esc_url($base_url) . '">' . esc_html__('Reset', 'academy-lesson-manager') . '</a>';
        }

        echo '</form>';
    }

    private function render_table() {
        $user_id    = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;
        $event_type = isset($_GET['event_type']) ? sanitize_key(wp_unslash($_GET['event_type'])) : '';
        $paged      = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page   = 50;
        $offset     = ($paged - 1) * $per_page;

        $users_table = $this->wpdb->users;

        $where  = array('1=1');
        $params = array();

        if ($user_id > 0) {
            $where[]  = 'a.user_id = %d';
            $params[] = $user_id;
        }

        if ($event_type !== '' && array_key_exists($event_type, ALM_Essentials_Selection_Audit::event_type_labels())) {
            $where[]  = 'a.event_type = %s';
            $params[] = $event_type;
        }

        $where_sql = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} a WHERE {$where_sql}";
        if (!empty($params)) {
            $total = (int) $this->wpdb->get_var($this->wpdb->prepare($count_sql, $params));
        } else {
            $total = (int) $this->wpdb->get_var($count_sql);
        }

        $labels = ALM_Essentials_Selection_Audit::event_type_labels();

        $list_sql = "SELECT a.*, 
                st.user_login AS student_login, 
                st.display_name AS student_display,
                ac.user_login AS actor_login,
                ac.display_name AS actor_display
            FROM {$this->table_name} a
            LEFT JOIN {$users_table} st ON st.ID = a.user_id
            LEFT JOIN {$users_table} ac ON ac.ID = a.actor_user_id AND a.actor_user_id > 0
            WHERE {$where_sql}
            ORDER BY a.id DESC
            LIMIT %d OFFSET %d";

        $list_params = $params;
        $list_params[] = $per_page;
        $list_params[] = $offset;

        $rows = $this->wpdb->get_results($this->wpdb->prepare($list_sql, $list_params));

        $total_pages = $total > 0 ? (int) ceil($total / $per_page) : 1;

        echo '<p><strong>' . esc_html(sprintf(
            /* translators: %d: number of log rows */
            _n('%d row', '%d rows', $total, 'academy-lesson-manager'),
            $total
        )) . '</strong></p>';

        if (empty($rows)) {
            echo '<p>' . esc_html__('No log entries match your filters.', 'academy-lesson-manager') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th scope="col">' . esc_html__('Date', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . esc_html__('Student', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . esc_html__('Event', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . esc_html__('Delta', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . esc_html__('Balance after', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . esc_html__('Actor', 'academy-lesson-manager') . '</th>';
        echo '<th scope="col">' . esc_html__('Details', 'academy-lesson-manager') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $ev_label = isset($labels[ $row->event_type ]) ? $labels[ $row->event_type ] : $row->event_type;
            $student  = $row->student_login ? $row->student_login : ('#' . (int) $row->user_id);
            if (!empty($row->student_display)) {
                $student .= ' — ' . $row->student_display;
            }
            $student_link = add_query_arg(
                array(
                    'page'      => 'academy-manager-essentials-users',
                    'view_user' => (int) $row->user_id,
                ),
                admin_url('admin.php')
            );

            $actor = '—';
            if ((int) $row->actor_user_id === 0) {
                $actor = esc_html__('System', 'academy-lesson-manager');
            } elseif (!empty($row->actor_login)) {
                $actor = esc_html($row->actor_login);
                if (!empty($row->actor_display)) {
                    $actor .= ' — ' . esc_html($row->actor_display);
                }
            }

            $meta_out = '';
            if (!empty($row->meta)) {
                $decoded = json_decode($row->meta, true);
                if (is_array($decoded)) {
                    $meta_out = esc_html(wp_json_encode($decoded, JSON_UNESCAPED_SLASHES));
                } else {
                    $meta_out = esc_html($row->meta);
                }
            }

            echo '<tr>';
            echo '<td>' . esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $row->created_at)) . '</td>';
            echo '<td><a href="' . esc_url($student_link) . '">' . esc_html($student) . '</a></td>';
            echo '<td>' . esc_html($ev_label) . '</td>';
            echo '<td>' . esc_html((string) (int) $row->delta) . '</td>';
            echo '<td>' . esc_html((string) (int) $row->balance_after) . '</td>';
            echo '<td>' . $actor . '</td>';
            echo '<td style="max-width: 280px; word-break: break-word;">' . ($meta_out !== '' ? $meta_out : '—') . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        if ($total_pages > 1) {
            $link_args = array(
                'page' => 'academy-manager-essentials-selection-log',
                'paged' => '%#%',
            );
            if ($user_id > 0) {
                $link_args['user_id'] = $user_id;
            }
            if ($event_type !== '') {
                $link_args['event_type'] = $event_type;
            }
            $page_links = paginate_links(array(
                'base'      => add_query_arg($link_args, admin_url('admin.php')),
                'format'    => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total'     => $total_pages,
                'current'   => $paged,
            ));
            if ($page_links) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
            }
        }
    }
}
