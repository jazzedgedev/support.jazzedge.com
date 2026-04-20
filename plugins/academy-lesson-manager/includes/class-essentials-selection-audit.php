<?php
/**
 * Essentials selection credit audit log
 *
 * @package Academy_Lesson_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Essentials_Selection_Audit {

    /**
     * @var bool|null
     */
    private static $table_exists = null;

    /**
     * Record a balance-changing event.
     *
     * @param int         $user_id       Essentials member (student) user ID.
     * @param string      $event_type    Slug: initialize, monthly_grant, manual_grant, count_set, count_adjust, lesson_selected.
     * @param int         $delta         Change in available credits (negative when spent).
     * @param int         $balance_after Credits available after this event.
     * @param int|null    $actor_user_id User who caused the event; 0 = system/cron; null = current user if logged in, else 0.
     * @param array|null  $meta          Optional context (e.g. lesson_id, previous balance).
     * @return bool True if a row was inserted.
     */
    public static function log($user_id, $event_type, $delta, $balance_after, $actor_user_id = null, $meta = null) {
        global $wpdb;

        $user_id = absint($user_id);
        if (!$user_id || !self::table_exists()) {
            return false;
        }

        $event_type = sanitize_key($event_type);
        if ($event_type === '') {
            return false;
        }

        if ($actor_user_id === null) {
            $actor_user_id = is_user_logged_in() ? get_current_user_id() : 0;
        } else {
            $actor_user_id = absint($actor_user_id);
        }

        $table = $wpdb->prefix . 'alm_essentials_selection_audit';

        $meta_json = null;
        if ($meta !== null && $meta !== array()) {
            $meta_json = wp_json_encode($meta);
        }

        $ok = $wpdb->insert(
            $table,
            array(
                'user_id'       => $user_id,
                'event_type'    => $event_type,
                'delta'         => intval($delta),
                'balance_after' => intval($balance_after),
                'actor_user_id' => $actor_user_id,
                'meta'          => $meta_json,
                'created_at'    => current_time('mysql'),
            ),
            array('%d', '%s', '%d', '%d', '%d', '%s', '%s')
        );

        return $ok !== false;
    }

    /**
     * @return bool
     */
    public static function table_exists() {
        global $wpdb;
        if (self::$table_exists !== null) {
            return self::$table_exists;
        }
        $t = $wpdb->prefix . 'alm_essentials_selection_audit';
        self::$table_exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $t)) === $t);
        return self::$table_exists;
    }

    /**
     * Human-readable labels for event_type (for admin UI).
     *
     * @return array<string,string>
     */
    public static function event_type_labels() {
        return array(
            'initialize'    => __('Membership initialized (first credit)', 'academy-lesson-manager'),
            'monthly_grant' => __('Automatic monthly grant (cron)', 'academy-lesson-manager'),
            'manual_grant'  => __('Manual grant (admin)', 'academy-lesson-manager'),
            'count_set'     => __('Available count set (admin)', 'academy-lesson-manager'),
            'count_adjust'  => __('Available count adjusted (admin)', 'academy-lesson-manager'),
            'lesson_selected' => __('Credit used (lesson added to library)', 'academy-lesson-manager'),
        );
    }
}
