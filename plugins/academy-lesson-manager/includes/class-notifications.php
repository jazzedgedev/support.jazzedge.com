<?php
/**
 * Notifications Manager
 *
 * Provides CRUD helpers for Academy notifications along with
 * user-specific read tracking utilities.
 *
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Notifications_Manager {

    /**
     * Default notification category slug
     */
    const DEFAULT_CATEGORY = 'site_update';

    /**
     * WordPress database instance
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Notifications table name
     *
     * @var string
     */
    private $notifications_table;

    /**
     * Notification reads table name
     *
     * @var string
     */
    private $reads_table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

        $database = new ALM_Database();
        $this->notifications_table = $database->get_table_name('notifications');
        $this->reads_table = $database->get_table_name('notification_reads');
    }

    /**
     * Return the available notification categories
     *
     * @return array
     */
    public static function get_category_palette() {
        return array(
            'site_alert' => array(
                'label' => __('Critical Alert', 'academy-lesson-manager'),
                'background' => '#fee2e2',
                'text' => '#dc2626',
                'border' => '#fecaca',
            ),
            'new_lesson' => array(
                'label' => __('New Lesson', 'academy-lesson-manager'),
                'background' => '#dbeafe',
                'text' => '#2563eb',
                'border' => '#93c5fd',
            ),
            'new_feature' => array(
                'label' => __('New Feature', 'academy-lesson-manager'),
                'background' => '#dcfce7',
                'text' => '#15803d',
                'border' => '#86efac',
            ),
            'site_update' => array(
                'label' => __('Site Update', 'academy-lesson-manager'),
                'background' => '#fed7aa',
                'text' => '#c2410c',
                'border' => '#fdba74',
            ),
            'event_update' => array(
                'label' => __('Event Update', 'academy-lesson-manager'),
                'background' => '#f3e8ff',
                'text' => '#9333ea',
                'border' => '#c084fc',
            ),
            'promotion' => array(
                'label' => __('Promotion', 'academy-lesson-manager'),
                'background' => '#fef3c7',
                'text' => '#ca8a04',
                'border' => '#facc15',
            ),
            'vacation_notice' => array(
                'label' => __('Vacation Notice', 'academy-lesson-manager'),
                'background' => '#e0f2fe',
                'text' => '#0284c7',
                'border' => '#7dd3fc',
            ),
        );
    }

    /**
     * Retrieve notifications
     *
     * @param array $args
     * @return array
     */
    public function get_notifications($args = array()) {
        $defaults = array(
            'status' => 'all', // all|active|inactive
            'limit' => 20,
            'offset' => 0,
            'order' => 'DESC',
            'only_published' => false,
            'category' => '',
        );
        $args = wp_parse_args($args, $defaults);

        $where = array();
        if ($args['status'] === 'active') {
            $where[] = 'is_active = 1';
        } elseif ($args['status'] === 'inactive') {
            $where[] = 'is_active = 0';
        }

        if (!empty($args['only_published'])) {
            $where[] = $this->wpdb->prepare('publish_at <= %s', current_time('mysql'));
        }

        if (!empty($args['category'])) {
            $where[] = $this->wpdb->prepare('category = %s', sanitize_key($args['category']));
        }

        $sql = "SELECT * FROM {$this->notifications_table}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY publish_at {$order}, ID {$order}";

        if (!empty($args['limit'])) {
            $sql .= $this->wpdb->prepare(' LIMIT %d OFFSET %d', absint($args['limit']), absint($args['offset']));
        }

        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get single notification
     *
     * @param int $notification_id
     * @return array|null
     */
    public function get_notification($notification_id) {
        if (empty($notification_id)) {
            return null;
        }

        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->notifications_table} WHERE ID = %d",
                $notification_id
            ),
            ARRAY_A
        );
    }

    /**
     * Save notification (insert or update)
     *
     * @param array $data
     * @return int|WP_Error
     */
    public function save_notification($data) {
        $defaults = array(
            'ID' => 0,
            'title' => '',
            'content' => '',
            'link_label' => '',
            'link_url' => '',
            'is_active' => 1,
            'show_popup' => 0,
            'publish_at' => current_time('mysql'),
            'category' => self::DEFAULT_CATEGORY,
        );

        $data = wp_parse_args($data, $defaults);

        if (empty($data['title'])) {
            return new WP_Error('alm_notification_title_required', __('A title is required.', 'academy-lesson-manager'));
        }

        $publish_timestamp = !empty($data['publish_at']) ? strtotime($data['publish_at']) : false;
        $categories = self::get_category_palette();
        $category = sanitize_key($data['category']);
        if (empty($category) || !isset($categories[$category])) {
            $category = self::DEFAULT_CATEGORY;
        }

        $prepared = array(
            'title' => sanitize_text_field($data['title']),
            'content' => wp_kses_post($data['content']),
            'link_label' => sanitize_text_field($data['link_label']),
            'link_url' => esc_url_raw($data['link_url']),
            'is_active' => intval($data['is_active']) ? 1 : 0,
            'show_popup' => intval($data['show_popup']) ? 1 : 0,
            'publish_at' => $publish_timestamp ? date('Y-m-d H:i:s', $publish_timestamp) : current_time('mysql'),
            'category' => $category,
        );

        if (!empty($data['ID'])) {
            $updated = $this->wpdb->update(
                $this->notifications_table,
                $prepared,
                array('ID' => intval($data['ID'])),
                array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'),
                array('%d')
            );

            return false === $updated ? new WP_Error('alm_notification_update_failed', __('Unable to update notification.', 'academy-lesson-manager')) : intval($data['ID']);
        }

        $inserted = $this->wpdb->insert(
            $this->notifications_table,
            $prepared,
            array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
        );

        if (false === $inserted) {
            return new WP_Error('alm_notification_insert_failed', __('Unable to create notification.', 'academy-lesson-manager'));
        }

        return intval($this->wpdb->insert_id);
    }

    /**
     * Delete notification (and related reads)
     *
     * @param int $notification_id
     * @return bool
     */
    public function delete_notification($notification_id) {
        if (empty($notification_id)) {
            return false;
        }

        $this->wpdb->delete($this->reads_table, array('notification_id' => intval($notification_id)), array('%d'));
        $deleted = $this->wpdb->delete($this->notifications_table, array('ID' => intval($notification_id)), array('%d'));

        return $deleted !== false;
    }

    /**
     * Toggle notification status
     *
     * @param int $notification_id
     * @param bool|int $is_active
     * @return bool
     */
    public function set_notification_status($notification_id, $is_active = true) {
        if (empty($notification_id)) {
            return false;
        }

        $updated = $this->wpdb->update(
            $this->notifications_table,
            array('is_active' => $is_active ? 1 : 0),
            array('ID' => intval($notification_id)),
            array('%d'),
            array('%d')
        );

        return $updated !== false;
    }

    /**
     * Fetch the most recent notification, optionally scoped to unread for a user
     *
     * @param array $args
     * @return array|null
     */
    public function get_latest_notification($args = array()) {
        $defaults = array(
            'status' => 'active',
            'only_published' => true,
            'unread_for' => 0,
        );
        $args = wp_parse_args($args, $defaults);

        $where = array();
        $joins = '';

        if ($args['status'] === 'active') {
            $where[] = 'n.is_active = 1';
        } elseif ($args['status'] === 'inactive') {
            $where[] = 'n.is_active = 0';
        }

        if (!empty($args['only_published'])) {
            $where[] = $this->wpdb->prepare('n.publish_at <= %s', current_time('mysql'));
        }

        if (!empty($args['unread_for'])) {
            $joins = $this->wpdb->prepare(
                " LEFT JOIN {$this->reads_table} r ON n.ID = r.notification_id AND r.user_id = %d",
                intval($args['unread_for'])
            );
            $where[] = 'r.ID IS NULL';
        }

        $sql = "SELECT n.* FROM {$this->notifications_table} n{$joins}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY n.publish_at DESC, n.ID DESC LIMIT 1';

        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get unread notification count for user
     *
     * @param int $user_id
     * @return int
     */
    public function get_unread_count($user_id) {
        if (empty($user_id)) {
            return 0;
        }

        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->notifications_table} n
             LEFT JOIN {$this->reads_table} r ON n.ID = r.notification_id AND r.user_id = %d
             WHERE n.is_active = 1
               AND n.publish_at <= %s
               AND r.ID IS NULL",
            intval($user_id),
            current_time('mysql')
        );

        return intval($this->wpdb->get_var($sql));
    }

    /**
     * Mark provided notifications as read for the user
     *
     * @param array $notification_ids
     * @param int   $user_id
     */
    public function mark_notifications_read($notification_ids, $user_id) {
        if (empty($notification_ids) || empty($user_id)) {
            return;
        }

        $notification_ids = array_map('intval', (array) $notification_ids);
        $notification_ids = array_filter($notification_ids);

        if (empty($notification_ids)) {
            return;
        }

        $timestamp = current_time('mysql');
        foreach ($notification_ids as $notification_id) {
            $this->wpdb->replace(
                $this->reads_table,
                array(
                    'notification_id' => $notification_id,
                    'user_id' => intval($user_id),
                    'read_at' => $timestamp,
                ),
                array('%d', '%d', '%s')
            );
        }
    }

    /**
     * Mark all active notifications as read
     *
     * @param int $user_id
     */
    public function mark_all_active_as_read($user_id) {
        if (empty($user_id)) {
            return;
        }

        $notifications = $this->get_notifications(array(
            'status' => 'active',
            'only_published' => true,
            'limit' => 0,
        ));

        if (empty($notifications)) {
            return;
        }

        $ids = wp_list_pluck($notifications, 'ID');
        $this->mark_notifications_read($ids, $user_id);
    }

    /**
     * Get popup notification for a user (one that hasn't been shown yet)
     *
     * @param int $user_id
     * @return array|null
     */
    public function get_popup_notification($user_id) {
        if (empty($user_id)) {
            return null;
        }

        // Get notifications that are set to show as popup
        $notifications = $this->get_notifications(array(
            'status' => 'active',
            'only_published' => true,
            'limit' => 50, // Get enough to check which haven't been shown
            'order' => 'DESC',
        ));

        if (empty($notifications)) {
            return null;
        }

        // Filter to only those with show_popup = 1
        $popup_notifications = array_filter($notifications, function($notif) {
            return !empty($notif['show_popup']) && intval($notif['show_popup']) === 1;
        });

        if (empty($popup_notifications)) {
            return null;
        }

        // Get list of notification IDs that have already been shown as popups to this user
        $shown_popups = get_user_meta($user_id, 'alm_notification_popups_shown', true);
        if (!is_array($shown_popups)) {
            $shown_popups = array();
        }

        // Find the first popup notification that hasn't been shown yet
        foreach ($popup_notifications as $notification) {
            if (!in_array(intval($notification['ID']), $shown_popups)) {
                return $notification;
            }
        }

        return null;
    }

    /**
     * Mark a notification popup as shown for a user
     *
     * @param int $notification_id
     * @param int $user_id
     */
    public function mark_popup_shown($notification_id, $user_id) {
        if (empty($notification_id) || empty($user_id)) {
            return;
        }

        $shown_popups = get_user_meta($user_id, 'alm_notification_popups_shown', true);
        if (!is_array($shown_popups)) {
            $shown_popups = array();
        }

        $notification_id = intval($notification_id);
        if (!in_array($notification_id, $shown_popups)) {
            $shown_popups[] = $notification_id;
            update_user_meta($user_id, 'alm_notification_popups_shown', $shown_popups);
        }
    }
}

