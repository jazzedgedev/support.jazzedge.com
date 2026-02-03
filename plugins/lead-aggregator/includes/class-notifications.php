<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Notifications {
    private $database;
    private $hook = 'lead_aggregator_followup_cron';

    public function __construct($database) {
        $this->database = $database;
        add_action($this->hook, array($this, 'send_followup_emails'));
    }

    public function schedule_cron() {
        if (!wp_next_scheduled($this->hook)) {
            wp_schedule_event(time(), 'hourly', $this->hook);
        }
    }

    public function clear_cron() {
        $timestamp = wp_next_scheduled($this->hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->hook);
        }
    }

    private function get_dashboard_url() {
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'lead-aggregator-dashboard.php',
            'number' => 1,
        ));
        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }
        return home_url('/');
    }

    private function build_followup_email($user, $leads) {
        $logo_id = (int) get_option('lead_aggregator_app_logo_id', 0);
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
        $dashboard_url = $this->get_dashboard_url();

        $rows = '';
        foreach ($leads as $lead) {
            $name = trim($lead['first_name'] . ' ' . $lead['last_name']);
            if (!$name) {
                $name = $lead['email'] ? $lead['email'] : 'Lead #' . $lead['id'];
            }
            $followup = $lead['followup_at'] ? $lead['followup_at'] : 'n/a';
            $due = $lead['due_at'] ? $lead['due_at'] : 'n/a';
            $source = $lead['source'] ? $lead['source'] : 'manual';
            $lead_url = add_query_arg('lead_id', (int) $lead['id'], $dashboard_url);

            $rows .= '<tr>' .
                '<td style="padding:10px 0;border-bottom:1px solid #e2e8f0;">' .
                '<div style="font-weight:600;color:#0f172a;">' . esc_html($name) . '</div>' .
                '<div style="color:#64748b;font-size:13px;">' . esc_html($lead['email']) . '</div>' .
                '</td>' .
                '<td style="padding:10px 0;border-bottom:1px solid #e2e8f0;color:#334155;font-size:13px;">' . esc_html($followup) . '</td>' .
                '<td style="padding:10px 0;border-bottom:1px solid #e2e8f0;color:#334155;font-size:13px;">' . esc_html($due) . '</td>' .
                '<td style="padding:10px 0;border-bottom:1px solid #e2e8f0;color:#334155;font-size:13px;">' . esc_html($source) . '</td>' .
                '<td style="padding:10px 0;border-bottom:1px solid #e2e8f0;">' .
                '<a href="' . esc_url($lead_url) . '" style="background:#2563eb;color:#ffffff;text-decoration:none;padding:8px 12px;border-radius:6px;font-size:13px;display:inline-block;">Follow up</a>' .
                '</td>' .
                '</tr>';
        }

        $logo_html = $logo_url ? '<img src="' . esc_url($logo_url) . '" alt="" style="max-height:40px;display:block;">' : '<div style="font-weight:700;font-size:18px;color:#0f172a;">Lead Aggregator</div>';

        $html = '<!doctype html><html><body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;">' .
            '<div style="max-width:700px;margin:32px auto;background:#ffffff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">' .
                '<div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;">' . $logo_html . '</div>' .
                '<div style="padding:24px;">' .
                    '<h2 style="margin:0 0 8px;font-size:20px;color:#0f172a;">Follow-ups due</h2>' .
                    '<p style="margin:0 0 16px;color:#475569;font-size:14px;">Hi ' . esc_html($user->display_name) . ', you have leads that need a follow-up.</p>' .
                    '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">' .
                        '<thead><tr>' .
                            '<th align="left" style="font-size:12px;text-transform:uppercase;color:#94a3b8;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">Lead</th>' .
                            '<th align="left" style="font-size:12px;text-transform:uppercase;color:#94a3b8;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">Followup</th>' .
                            '<th align="left" style="font-size:12px;text-transform:uppercase;color:#94a3b8;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">Due</th>' .
                            '<th align="left" style="font-size:12px;text-transform:uppercase;color:#94a3b8;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">Source</th>' .
                            '<th align="left" style="font-size:12px;text-transform:uppercase;color:#94a3b8;border-bottom:1px solid #e2e8f0;padding-bottom:8px;"></th>' .
                        '</tr></thead>' .
                        '<tbody>' . $rows . '</tbody>' .
                    '</table>' .
                    '<div style="margin-top:20px;">' .
                        '<a href="' . esc_url($dashboard_url) . '" style="color:#2563eb;text-decoration:none;font-size:13px;">Open dashboard</a>' .
                    '</div>' .
                '</div>' .
            '</div>' .
            '</body></html>';

        return $html;
    }

    public function send_followup_emails() {
        $enabled = (int) get_option('lead_aggregator_notify_enabled', 1);
        if (!$enabled) {
            return;
        }

        $cutoff = current_time('mysql');
        $leads = $this->database->get_due_leads($cutoff);
        if (empty($leads)) {
            return;
        }

        $by_user = array();
        foreach ($leads as $lead) {
            $by_user[$lead['user_id']][] = $lead;
        }

        foreach ($by_user as $user_id => $user_leads) {
            $user = get_user_by('id', $user_id);
            if (!$user) {
                continue;
            }
            if (!$this->should_send_digest($user_id)) {
                continue;
            }

            $subject = 'Lead follow-ups due';
            $message = $this->build_followup_email($user, $user_leads);
            $headers = array('Content-Type: text/html; charset=UTF-8');

            $sent = wp_mail($user->user_email, $subject, $message, $headers);
            $this->mark_digest_sent($user_id);
            $this->database->log_email(array(
                'user_id' => $user_id,
                'recipient_email' => $user->user_email,
                'subject' => $subject,
                'message' => $message,
                'status' => $sent ? 'sent' : 'failed',
                'error_message' => $sent ? null : 'wp_mail returned false',
                'meta' => array(
                    'lead_count' => count($user_leads),
                    'lead_ids' => array_map(function ($lead) { return (int) $lead['id']; }, $user_leads),
                ),
            ));
        }
    }

    private function should_send_digest($user_id) {
        $timezone = $this->get_user_timezone($user_id);
        $time = $this->get_user_digest_time($user_id);
        $now = new DateTime('now', $timezone);
        $target = DateTime::createFromFormat('Y-m-d H:i', $now->format('Y-m-d') . ' ' . $time, $timezone);
        if (!$target) {
            return false;
        }

        if ($now < $target) {
            return false;
        }

        $last_sent = get_user_meta($user_id, 'lead_aggregator_digest_last_sent', true);
        if ($last_sent === $now->format('Y-m-d')) {
            return false;
        }

        return true;
    }

    private function mark_digest_sent($user_id) {
        $timezone = $this->get_user_timezone($user_id);
        $now = new DateTime('now', $timezone);
        update_user_meta($user_id, 'lead_aggregator_digest_last_sent', $now->format('Y-m-d'));
    }

    private function get_user_timezone($user_id) {
        $timezone = get_user_meta($user_id, 'lead_aggregator_digest_timezone', true);
        if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
            return new DateTimeZone($timezone);
        }
        $default = get_option('lead_aggregator_digest_timezone_default', wp_timezone_string());
        if (!$default || !in_array($default, timezone_identifiers_list(), true)) {
            return wp_timezone();
        }
        return new DateTimeZone($default);
    }

    private function get_user_digest_time($user_id) {
        $time = get_user_meta($user_id, 'lead_aggregator_digest_time', true);
        if (!$time) {
            $time = get_option('lead_aggregator_digest_time_default', '09:00');
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return '09:00';
        }
        return $time;
    }
}
