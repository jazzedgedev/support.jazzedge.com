<?php
if (!defined('ABSPATH')) exit;

/**
 * Inbound FluentCart (Jazzedge Academy) webhooks + local order mirror + request log.
 */
class JECM_FluentCart_Webhook {

    public static function log_table() {
        global $wpdb;
        return $wpdb->prefix . 'jecm_webhook_log';
    }

    public static function orders_table() {
        global $wpdb;
        return $wpdb->prefix . 'jecm_fc_orders';
    }

    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $log     = self::log_table();
        $orders  = self::orders_table();

        $sql_log = "CREATE TABLE {$log} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            remote_ip VARCHAR(45) NOT NULL DEFAULT '',
            auth_ok TINYINT(1) NOT NULL DEFAULT 0,
            processed_ok TINYINT(1) NOT NULL DEFAULT 0,
            http_status SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            message VARCHAR(500) NOT NULL DEFAULT '',
            order_uuid VARCHAR(64) NULL,
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            request_body LONGTEXT NULL,
            user_agent VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            KEY created_at (created_at),
            KEY auth_ok (auth_ok),
            KEY order_uuid (order_uuid(64)),
            KEY customer_email (customer_email(191))
        ) {$charset};";

        $sql_orders = "CREATE TABLE {$orders} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_uuid VARCHAR(64) NOT NULL,
            academy_order_id BIGINT(20) UNSIGNED NULL,
            customer_id BIGINT(20) UNSIGNED NULL,
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            status VARCHAR(64) NOT NULL DEFAULT '',
            payment_status VARCHAR(64) NOT NULL DEFAULT '',
            currency VARCHAR(16) NOT NULL DEFAULT '',
            total_amount BIGINT(20) NULL,
            receipt_number VARCHAR(128) NOT NULL DEFAULT '',
            invoice_no VARCHAR(128) NOT NULL DEFAULT '',
            completed_at DATETIME NULL,
            payload_json LONGTEXT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_uuid (order_uuid),
            KEY customer_email (customer_email(191)),
            KEY academy_order_id (academy_order_id),
            KEY updated_at (updated_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_log);
        dbDelta($sql_orders);
    }

    public static function bootstrap() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes() {
        register_rest_route(
            'jecm/v1',
            '/fluentcart',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array(__CLASS__, 'rest_handle'),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * @return string Secret (may be empty until configured).
     */
    public static function get_secret() {
        $s = get_option('jecm_fc_webhook_secret', '');
        return is_string($s) ? $s : '';
    }

    /**
     * @param array<string, mixed> $args
     * @return list<array<string, mixed>>
     */
    public static function get_webhook_logs($limit = 25, $offset = 0, $args = array()) {
        global $wpdb;
        $table = self::log_table();
        $limit = max(1, min(100, (int) $limit));
        $offset = max(0, (int) $offset);

        $where  = '1=1';
        $params = array();

        if (!empty($args['auth_only_ok'])) {
            $where .= ' AND auth_ok = 1';
        }
        if (!empty($args['email'])) {
            $where .= ' AND customer_email = %s';
            $params[] = sanitize_email($args['email']);
        }

        $sql    = "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A) ?: array();
    }

    public static function get_webhook_log_total($args = array()) {
        global $wpdb;
        $table = self::log_table();
        $where  = '1=1';
        $params = array();

        if (!empty($args['auth_only_ok'])) {
            $where .= ' AND auth_ok = 1';
        }
        if (!empty($args['email'])) {
            $where .= ' AND customer_email = %s';
            $params[] = sanitize_email($args['email']);
        }

        if (empty($params)) {
            return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$where}");
        }

        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}", $params));
    }

    public static function purge_webhook_logs() {
        global $wpdb;
        $wpdb->query('TRUNCATE TABLE ' . self::log_table());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function get_orders_by_email($email, $limit = 100) {
        global $wpdb;
        $email = sanitize_email((string) $email);
        if ($email === '') {
            return array();
        }
        $table = self::orders_table();
        $lim   = max(1, min(500, (int) $limit));

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE customer_email = %s ORDER BY updated_at DESC, id DESC LIMIT %d",
                $email,
                $lim
            ),
            ARRAY_A
        ) ?: array();
    }

    /**
     * Best-effort display name from stored webhook payloads (customer / order billing fields).
     *
     * @param list<array<string, mixed>> $order_rows
     */
    public static function guess_display_name_from_orders($order_rows, $fallback_email) {
        foreach ($order_rows as $row) {
            $raw = isset($row['payload_json']) ? (string) $row['payload_json'] : '';
            if ($raw === '') {
                continue;
            }
            $j = json_decode($raw, true);
            if (!is_array($j)) {
                continue;
            }
            $c = isset($j['customer']) && is_array($j['customer']) ? $j['customer'] : null;
            if (is_array($c)) {
                $fn = trim((string) ($c['first_name'] ?? ''));
                $ln = trim((string) ($c['last_name'] ?? ''));
                $n  = trim($fn . ' ' . $ln);
                if ($n !== '') {
                    return $n;
                }
            }
            $o = isset($j['order']) && is_array($j['order']) ? $j['order'] : null;
            if (is_array($o)) {
                $fn = trim((string) ($o['billing_first_name'] ?? $o['first_name'] ?? ''));
                $ln = trim((string) ($o['billing_last_name'] ?? $o['last_name'] ?? ''));
                $n  = trim($fn . ' ' . $ln);
                if ($n !== '') {
                    return $n;
                }
            }
        }

        return is_email($fallback_email) ? $fallback_email : '';
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public static function rest_handle($request) {
        global $wpdb;

        $raw_body = $request->get_body();
        if (!is_string($raw_body)) {
            $raw_body = '';
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        if (strlen($ua) > 250) {
            $ua = substr($ua, 0, 250);
        }

        $secret = self::get_secret();
        $provided = self::extract_provided_secret($request);

        $auth_ok = ($secret !== '' && $provided !== '' && hash_equals($secret, $provided));
        $http    = 200;
        $msg     = 'OK';
        $proc_ok = 0;
        $uuid    = null;
        $email   = '';

        if ($secret === '') {
            $auth_ok = false;
            $http    = 503;
            $msg     = 'Webhook secret not configured on this site.';
        } elseif (!$auth_ok) {
            $http = 401;
            $msg  = 'Invalid or missing webhook secret.';
        } else {
            $decoded = null;
            if ($raw_body !== '') {
                $decoded = json_decode($raw_body, true);
            }
            if ($raw_body !== '' && !is_array($decoded)) {
                $http    = 400;
                $msg     = 'Body is not valid JSON.';
                $auth_ok = true;
            } elseif (is_array($decoded)) {
                $parsed = self::parse_fluentcart_payload($decoded);
                $uuid   = $parsed['order_uuid'];
                $email  = $parsed['customer_email'];

                if ($uuid === null || $uuid === '') {
                    $http = 422;
                    $msg  = 'Could not determine order UUID from payload.';
                } else {
                    $saved = self::upsert_order($parsed, $decoded);
                    if ($saved) {
                        $proc_ok = 1;
                        $msg     = 'Order stored.';
                    } else {
                        $http = 500;
                        $msg  = 'Failed to save order row.';
                    }
                }
            } else {
                $msg = 'Empty body accepted.';
            }
        }

        $wpdb->insert(
            self::log_table(),
            array(
                'remote_ip'      => $ip,
                'auth_ok'        => $auth_ok ? 1 : 0,
                'processed_ok'   => $proc_ok,
                'http_status'    => $http,
                'message'        => substr($msg, 0, 500),
                'order_uuid'     => $uuid !== null && $uuid !== '' ? substr($uuid, 0, 64) : '',
                'customer_email' => $email !== '' ? $email : '',
                'request_body'   => self::maybe_truncate_body($raw_body),
                'user_agent'     => $ua,
            ),
            array('%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );

        $response = new \WP_REST_Response(
            array(
                'success' => ($http >= 200 && $http < 300),
                'message' => $msg,
            ),
            $http
        );
        return $response;
    }

    private static function maybe_truncate_body($raw) {
        $max = 65535;
        if (strlen($raw) <= $max) {
            return $raw;
        }
        return substr($raw, 0, $max) . "\n…[truncated]";
    }

    /**
     * @param \WP_REST_Request $request
     */
    private static function extract_provided_secret($request) {
        $h = $request->get_header('x-jecm-webhook-secret');
        if (is_string($h) && $h !== '') {
            return trim($h);
        }
        $h2 = $request->get_header('x-webhook-secret');
        if (is_string($h2) && $h2 !== '') {
            return trim($h2);
        }
        $auth = $request->get_header('authorization');
        if (is_string($auth) && preg_match('/Bearer\s+(.+)$/i', $auth, $m)) {
            return trim($m[1]);
        }
        $q = $request->get_param('secret');
        if (is_string($q) && $q !== '') {
            return trim($q);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $data
     * @return array{order: array<string, mixed>|null, customer: array<string, mixed>|null}
     */
    private static function locate_order_and_customer(array $data) {
        $pairs = array(
            array('base' => $data, 'order_key' => 'order'),
            array('base' => isset($data['data']) && is_array($data['data']) ? $data['data'] : null, 'order_key' => 'order'),
            array('base' => isset($data['event_data']) && is_array($data['event_data']) ? $data['event_data'] : null, 'order_key' => 'order'),
            array('base' => isset($data['payload']) && is_array($data['payload']) ? $data['payload'] : null, 'order_key' => 'order'),
        );

        foreach ($pairs as $pair) {
            if (!is_array($pair['base']) || empty($pair['order_key'])) {
                continue;
            }
            $base = $pair['base'];
            if (!isset($base[$pair['order_key']]) || !is_array($base[$pair['order_key']])) {
                continue;
            }
            $order = $base[$pair['order_key']];
            $customer = null;
            if (isset($base['customer']) && is_array($base['customer'])) {
                $customer = $base['customer'];
            } elseif (isset($order['customer']) && is_array($order['customer'])) {
                $customer = $order['customer'];
            }
            return array('order' => $order, 'customer' => $customer);
        }

        $order = self::deep_first_assoc_with_keys($data, array('uuid'));
        if (is_array($order)) {
            return array('order' => $order, 'customer' => null);
        }

        return array('order' => null, 'customer' => null);
    }

    /**
     * @param array<string, mixed> $arr
     * @param list<string> $required_keys
     * @return array<string, mixed>|null
     */
    private static function deep_first_assoc_with_keys(array $arr, array $required_keys) {
        $has = true;
        foreach ($required_keys as $k) {
            if (!array_key_exists($k, $arr)) {
                $has = false;
                break;
            }
        }
        if ($has) {
            return $arr;
        }
        foreach ($arr as $v) {
            if (is_array($v)) {
                $found = self::deep_first_assoc_with_keys($v, $required_keys);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        return null;
    }

    /**
     * @param array<string, mixed> $decoded
     * @return array{
     *   order_uuid: ?string,
     *   academy_order_id: ?int,
     *   customer_id: ?int,
     *   customer_email: string,
     *   status: string,
     *   payment_status: string,
     *   currency: string,
     *   total_amount: ?int,
     *   receipt_number: string,
     *   invoice_no: string,
     *   completed_at: ?string
     * }
     */
    public static function parse_fluentcart_payload(array $decoded) {
        $loc = self::locate_order_and_customer($decoded);
        $order = $loc['order'];
        $cust  = $loc['customer'];

        if (!is_array($order)) {
            return array(
                'order_uuid'      => null,
                'academy_order_id'=> null,
                'customer_id'     => null,
                'customer_email'  => '',
                'status'          => '',
                'payment_status'  => '',
                'currency'        => '',
                'total_amount'    => null,
                'receipt_number'  => '',
                'invoice_no'      => '',
                'completed_at'    => null,
            );
        }

        $uuid = self::first_string($order, array('uuid', 'order_uuid'));
        if ($uuid === '' && isset($order['id'])) {
            $uuid = 'id:' . (string) (int) $order['id'];
        }

        $email = '';
        if (is_array($cust)) {
            $email = self::first_string($cust, array('email', 'billing_email'));
        }
        if ($email === '') {
            $email = self::first_string($order, array('customer_email', 'billing_email', 'email'));
        }
        if ($email === '' && isset($order['customer']) && is_array($order['customer'])) {
            $email = self::first_string($order['customer'], array('email'));
        }

        $aid = self::first_int($order, array('id'));
        $cid = null;
        if (is_array($cust)) {
            $cid = self::first_int($cust, array('id', 'customer_id'));
        }
        if ($cid === null) {
            $cid = self::first_int($order, array('customer_id'));
        }

        $total = self::first_int($order, array('total_amount', 'total', 'grand_total'));
        $completed = self::first_scalar($order, array('completed_at', 'paid_at', 'created_at'));

        return array(
            'order_uuid'      => $uuid !== '' ? $uuid : null,
            'academy_order_id'=> $aid,
            'customer_id'     => $cid,
            'customer_email'  => sanitize_email($email) ?: '',
            'status'          => self::first_string($order, array('status', 'order_status')),
            'payment_status'  => self::first_string($order, array('payment_status')),
            'currency'        => self::first_string($order, array('currency')),
            'total_amount'    => $total,
            'receipt_number'  => self::first_string($order, array('receipt_number')),
            'invoice_no'      => self::first_string($order, array('invoice_no')),
            'completed_at'    => self::normalize_mysql_datetime($completed) ?: null,
        );
    }

    private static function first_string(array $arr, array $keys) {
        foreach ($keys as $k) {
            if (!isset($arr[$k])) {
                continue;
            }
            $v = $arr[$k];
            if (is_string($v) || is_numeric($v)) {
                $s = trim((string) $v);
                if ($s !== '') {
                    return $s;
                }
            }
        }
        return '';
    }

    private static function first_int(array $arr, array $keys) {
        foreach ($keys as $k) {
            if (!isset($arr[$k]) || $arr[$k] === '' || $arr[$k] === null) {
                continue;
            }
            if (is_numeric($arr[$k])) {
                return (int) $arr[$k];
            }
        }
        return null;
    }

    private static function first_scalar(array $arr, array $keys) {
        foreach ($keys as $k) {
            if (isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
                return $arr[$k];
            }
        }
        return null;
    }

    private static function normalize_mysql_datetime($v) {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            $ts = (int) $v;
            if ($ts > 20000000000) {
                $ts = (int) round($ts / 1000);
            }
            return $ts > 0 ? gmdate('Y-m-d H:i:s', $ts) : null;
        }
        $s = trim((string) $v);
        $ts = strtotime($s);
        if ($ts === false) {
            return null;
        }
        return gmdate('Y-m-d H:i:s', $ts);
    }

    /**
     * @param array<string, mixed> $parsed from parse_fluentcart_payload
     * @param array<string, mixed> $decoded full JSON
     */
    private static function upsert_order(array $parsed, array $decoded) {
        global $wpdb;
        $table = self::orders_table();
        $uuid  = $parsed['order_uuid'];
        if ($uuid === null || $uuid === '') {
            return false;
        }

        $row = array(
            'order_uuid'       => substr($uuid, 0, 64),
            'academy_order_id' => $parsed['academy_order_id'],
            'customer_id'      => $parsed['customer_id'],
            'customer_email'   => $parsed['customer_email'],
            'status'           => sanitize_text_field($parsed['status']),
            'payment_status'   => sanitize_text_field($parsed['payment_status']),
            'currency'         => sanitize_text_field(substr($parsed['currency'], 0, 16)),
            'total_amount'     => $parsed['total_amount'],
            'receipt_number'   => sanitize_text_field(substr($parsed['receipt_number'], 0, 128)),
            'invoice_no'       => sanitize_text_field(substr($parsed['invoice_no'], 0, 128)),
            'completed_at'     => $parsed['completed_at'],
            'payload_json'     => wp_json_encode($decoded),
        );

        foreach (array('academy_order_id', 'customer_id', 'total_amount', 'completed_at') as $nullable) {
            if (array_key_exists($nullable, $row) && $row[$nullable] === null) {
                unset($row[$nullable]);
            }
        }

        $formats = array();
        foreach ($row as $k => $v) {
            if ($k === 'academy_order_id' || $k === 'customer_id' || $k === 'total_amount') {
                $formats[] = '%d';
            } else {
                $formats[] = '%s';
            }
        }

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE order_uuid = %s",
            $row['order_uuid']
        ));

        if ($existing) {
            unset($row['order_uuid']);
            $formats_update = array();
            foreach ($row as $k => $v) {
                $formats_update[] = ($k === 'academy_order_id' || $k === 'customer_id' || $k === 'total_amount') ? '%d' : '%s';
            }
            $res = $wpdb->update($table, $row, array('id' => (int) $existing), $formats_update, array('%d'));
            return $res !== false;
        }

        $res = $wpdb->insert($table, $row, $formats);
        return $res !== false;
    }
}
