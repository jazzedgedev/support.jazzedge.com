<?php
if (!defined('ABSPATH')) exit;

class JECM_Keap_API {

    private $api_key;
    private $base_url    = 'https://api.infusionsoft.com/crm/rest/v1';
    private $base_url_v2 = 'https://api.infusionsoft.com/crm/rest/v2';

    /** Number of completed HTTP round-trips to Keap for this API client instance (Memberium-style per-request awareness). */
    private $keap_http_call_count = 0;

    /**
     * Latest quota / throttle header values merged from Keap REST responses (see Keap developer docs:
     * x-keap-product-quota-*, x-keap-product-throttle-*, x-keap-tenant-throttle-*).
     *
     * @var array<string, string>
     */
    private $keap_quota_snapshot = array();

    /** @var array<string, int>|null */
    private $field_model_cache = null;

    /** @var list<string> */
    private static $keap_quota_header_names = array(
        'x-keap-product-quota-limit',
        'x-keap-product-quota-time-unit',
        'x-keap-product-quota-interval',
        'x-keap-product-quota-available',
        'x-keap-product-quota-used',
        'x-keap-product-quota-expiry-time',
        'x-keap-product-throttle-limit',
        'x-keap-product-throttle-time-unit',
        'x-keap-product-throttle-interval',
        'x-keap-product-throttle-available',
        'x-keap-product-throttle-used',
        'x-keap-tenant-id',
        'x-keap-tenant-throttle-limit',
        'x-keap-tenant-throttle-time-unit',
        'x-keap-tenant-throttle-interval',
        'x-keap-tenant-throttle-available',
        'x-keap-tenant-throttle-used',
    );

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function get_keap_http_request_count(): int {
        return $this->keap_http_call_count;
    }

    /**
     * @return array<string, string>
     */
    public function get_keap_quota_snapshot() {
        return $this->keap_quota_snapshot;
    }

    /**
     * @param array|\WP_Error $response
     */
    private function record_keap_response_meta($response) {
        if (is_wp_error($response) || !is_array($response)) {
            return;
        }
        $this->keap_http_call_count++;
        foreach (self::$keap_quota_header_names as $name) {
            $val = wp_remote_retrieve_header($response, $name);
            if ($val !== '' && $val !== false && $val !== null) {
                $this->keap_quota_snapshot[$name] = is_array($val) ? implode(',', $val) : (string) $val;
            }
        }
    }

    private function http_get($url, $args = array()) {
        $response = call_user_func('wp_remote_get', $url, $args);
        $this->record_keap_response_meta($response);

        return $response;
    }

    private function http_post($url, $args = array()) {
        $response = call_user_func('wp_remote_post', $url, $args);
        $this->record_keap_response_meta($response);

        return $response;
    }

    private function http_request($url, $args = array()) {
        $response = call_user_func('wp_remote_request', $url, $args);
        $this->record_keap_response_meta($response);

        return $response;
    }

    /**
     * Single GET for contact record including custom_fields (reduces N+1 custom field calls).
     *
     * @return array{success: bool, message?: string, body?: array|null}
     */
    public function get_contact_full($contact_id) {
        $response = $this->http_get(
            $this->base_url . '/contacts/' . intval($contact_id) . '?optional_properties=custom_fields',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message(), 'body' => null);
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return array(
                'success' => false,
                'message' => 'HTTP ' . $code . ' — ' . wp_remote_retrieve_body($response),
                'body'    => null,
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return is_array($body)
            ? array('success' => true, 'body' => $body)
            : array('success' => false, 'message' => 'Invalid JSON from Keap.', 'body' => null);
    }

    /**
     * Resolve display/raw for a custom field using contact payload + model (no extra GET per field).
     *
     * @param array $body Contact JSON body (must include custom_fields when field exists).
     * @return array{success: bool, raw: string, display: string, message?: string}
     */
    public function interpret_custom_field_from_body(array $body, $field_name) {
        $model      = $this->get_field_model();
        $lookup_key = strtolower(ltrim((string) $field_name, '_'));

        if (!isset($model[ $lookup_key ])) {
            return array(
                'success' => false,
                'raw'     => '',
                'display' => '—',
                'message' => 'Field "' . $field_name . '" not found in Keap contact model.',
            );
        }

        $numeric_id = (int) $model[ $lookup_key ];

        foreach ($body['custom_fields'] ?? array() as $cf) {
            if ((int) ($cf['id'] ?? 0) === $numeric_id) {
                $raw     = isset($cf['content']) ? (string) $cf['content'] : '';
                $ts      = strtotime($raw);
                $display = ($ts && strlen($raw) > 6) ? date('F j, Y', $ts) : ($raw !== '' ? $raw : '(empty)');

                return array('success' => true, 'raw' => $raw, 'display' => $display);
            }
        }

        return array(
            'success' => false,
            'raw'     => '',
            'display' => '—',
            'message' => 'Field in model (id ' . $numeric_id . ') not returned on contact.',
        );
    }

    public function find_contact_id($email) {
        $response = $this->http_get(
            $this->base_url . '/contacts?email=' . urlencode($email) . '&limit=1',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['contacts'][0]['id'])) {
            return array('success' => false, 'message' => 'Contact not found in Keap.');
        }

        return array('success' => true, 'contact_id' => $body['contacts'][0]['id']);
    }

    /**
     * Create a contact via Keap REST v2.
     *
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @return array{success: bool, contact_id?: int, error?: string}
     */
    public function create_contact($email, $first_name = '', $last_name = '') {
        if (empty($this->api_key)) {
            return array('success' => false, 'error' => 'No API key.');
        }

        $body = array(
            'email_addresses' => array(
                array(
                    'email' => $email,
                    'field' => 'EMAIL1',
                ),
            ),
        );
        if ($first_name !== '') {
            $body['given_name'] = $first_name;
        }
        if ($last_name !== '') {
            $body['family_name'] = $last_name;
        }

        $response = $this->http_post(
            'https://api.infusionsoft.com/crm/rest/v2/contacts',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ),
                'body'    => wp_json_encode($body),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $resp_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($code === 200 || $code === 201) {
            $id = 0;
            if (is_array($resp_body)) {
                $id = (int) ($resp_body['id'] ?? $resp_body['contact_id'] ?? 0);
            }

            return array('success' => true, 'contact_id' => $id);
        }

        $msg = '';
        if (is_array($resp_body)) {
            $msg = (string) ($resp_body['message'] ?? $resp_body['error'] ?? '');
        }
        if ($msg === '') {
            $msg = 'HTTP ' . $code;
        }

        return array('success' => false, 'error' => $msg);
    }

    public function apply_tag($contact_id, $tag_id) {
        $response = $this->http_post(
            $this->base_url . '/contacts/' . intval($contact_id) . '/tags',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ),
                'body'    => wp_json_encode(array('tagIds' => array(intval($tag_id)))),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        return array(
            'success' => ($code >= 200 && $code < 300),
            'message' => 'HTTP ' . $code,
        );
    }

    /**
     * Permanently delete an order in Keap.
     * Tries REST v2 first (DELETE /crm/rest/v2/orders/{id}), then REST v1.
     *
     * @param int|string $order_id
     * @return array{success: bool, error?: string}
     */
    public function delete_order($order_id) {
        if (empty($this->api_key)) {
            return array('success' => false, 'error' => 'No API key.');
        }
        $order_id = is_numeric($order_id) ? (int) $order_id : $order_id;
        if ($order_id === '' || $order_id === null || (is_int($order_id) && $order_id < 1)) {
            return array('success' => false, 'error' => 'Invalid order ID.');
        }

        $v2 = $this->delete_order_rest_delete(
            $this->base_url_v2 . '/orders/' . rawurlencode((string) $order_id)
        );
        if (!empty($v2['success'])) {
            return $v2;
        }

        $v1 = $this->delete_order_rest_delete(
            $this->base_url . '/orders/' . (is_int($order_id) ? $order_id : rawurlencode((string) $order_id))
        );
        if (!empty($v1['success'])) {
            return $v1;
        }

        $e2 = $v2['error'] ?? 'Unknown error';
        $e1 = $v1['error'] ?? 'Unknown error';

        if ($e2 === $e1) {
            return array('success' => false, 'error' => $e1);
        }

        return array(
            'success' => false,
            'error'   => 'REST v2: ' . $e2 . ' — REST v1: ' . $e1,
        );
    }

    /**
     * @return array{success: bool, error?: string}
     */
    private function delete_order_rest_delete($url) {
        $response = $this->http_request(
            $url,
            array(
                'method'  => 'DELETE',
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ),
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code >= 200 && $code < 300) {
            return array('success' => true);
        }

        $body    = json_decode(wp_remote_retrieve_body($response), true);
        $raw_err = is_array($body) ? ($body['message'] ?? $body['error'] ?? $body['detail'] ?? '') : '';
        if (! is_string($raw_err)) {
            $raw_err = is_array($raw_err) ? wp_json_encode($raw_err) : (string) $raw_err;
        }
        $out = $raw_err !== '' ? $raw_err : ('HTTP ' . $code);

        return array('success' => false, 'error' => $out);
    }

    /**
     * Apply multiple tags to a contact in one request.
     *
     * @param int   $contact_id
     * @param int[] $tag_ids
     * @return array{success: bool, message: string}
     */
    public function apply_tags_batch($contact_id, array $tag_ids) {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $tag_ids),
                    static function ($n) {
                        return $n > 0;
                    }
                )
            )
        );
        if (empty($ids)) {
            return array(
                'success' => false,
                'message' => 'No valid tag IDs.',
            );
        }

        $response = $this->http_post(
            $this->base_url . '/contacts/' . intval($contact_id) . '/tags',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ),
                'body'    => wp_json_encode(array('tagIds' => $ids)),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);

        return array(
            'success' => ($code >= 200 && $code < 300),
            'message' => 'HTTP ' . $code . ( $code >= 200 && $code < 300 ? '' : ' — ' . wp_remote_retrieve_body($response) ),
        );
    }

    /**
     * Full tag list from Keap (paged). Cached 2 hours unless $force_refresh.
     *
     * @param bool $force_refresh
     * @return array{success: bool, tags?: array<int, array{id: int, name: string}>, cached?: bool, message?: string}
     */
    public function get_all_tags($force_refresh = false) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'No API key.',
            );
        }

        $transient_key = 'jecm_all_keap_tags';
        if ($force_refresh) {
            delete_transient($transient_key);
        }

        $cached = get_transient($transient_key);
        if (false !== $cached && is_array($cached)) {
            return array(
                'success' => true,
                'tags'    => $cached,
                'cached'  => true,
            );
        }

        $all    = array();
        $offset = 0;
        $limit  = 1000;

        while (true) {
            $query = http_build_query(
                array(
                    'limit'  => $limit,
                    'offset' => $offset,
                ),
                '',
                '&',
                PHP_QUERY_RFC3986
            );
            $response = $this->http_get(
                $this->base_url . '/tags?' . $query,
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $this->api_key,
                        'Accept'        => 'application/json',
                    ),
                    'timeout' => 30,
                )
            );

            if (is_wp_error($response)) {
                return array(
                    'success' => false,
                    'message' => $response->get_error_message(),
                );
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code < 200 || $code >= 300) {
                return array(
                    'success' => false,
                    'message' => 'HTTP ' . $code . ' — ' . wp_remote_retrieve_body($response),
                );
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            $batch = array();
            if (is_array($body) && !empty($body['tags']) && is_array($body['tags'])) {
                foreach ($body['tags'] as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $id = isset($row['id']) ? (int) $row['id'] : 0;
                    if ($id <= 0) {
                        continue;
                    }
                    $name = isset($row['name']) ? (string) $row['name'] : '';
                    if ($name === '') {
                        $name = 'Tag #' . $id;
                    }
                    $batch[] = array(
                        'id'   => $id,
                        'name' => $name,
                    );
                }
            }

            $all = array_merge($all, $batch);
            if (count($batch) < $limit) {
                break;
            }
            $offset += $limit;
        }

        set_transient($transient_key, $all, 2 * HOUR_IN_SECONDS);

        return array(
            'success' => true,
            'tags'    => $all,
            'cached'  => false,
        );
    }

    public function get_last_payment_date($contact_id) {
        $response = $this->http_get(
            $this->base_url . '/transactions?contact_id=' . intval($contact_id) . '&limit=200',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['transactions'])) {
            return array('success' => false, 'message' => 'No transactions found.');
        }

        // Filter to successful payments only and find the most recent
        $dates = array();
        foreach ($body['transactions'] as $tx) {
            if (!empty($tx['transaction_date'])) {
                $dates[] = $tx['transaction_date'];
            }
        }

        if (empty($dates)) {
            return array('success' => false, 'message' => 'No transaction dates found.');
        }

        rsort($dates);
        $last = $dates[0];

        // Format: 2024-03-15T00:00:00.000Z → March 15, 2024
        $ts = strtotime($last);
        $formatted = $ts ? date('F j, Y', $ts) : $last;

        return array('success' => true, 'date' => $formatted, 'raw' => $last);
    }

    /**
     * Human label for REST order `status` (used by get_recent_transactions).
     */
    private function format_transaction_status_display($status_raw) {
        $s = strtoupper(trim((string) $status_raw));
        switch ($s) {
            case 'PAID':
                return 'Paid';
            case 'DRAFT':
                return 'Unpaid';
            case 'OVERDUE':
                return 'Overdue';
            case 'SENT':
                return 'Invoice Sent';
            case 'REFUNDED':
            case 'PARTIAL_REFUND':
                return 'Refunded';
            case 'CANCELLED':
            case 'CANCELED':
                return 'Cancelled';
            default:
                if ($s === '') {
                    return '—';
                }

                return ucwords(strtolower((string) $status_raw));
        }
    }

    public function get_recent_transactions($contact_id, $limit = 10) {
        if (empty($this->api_key)) {
            return array(
                'success'      => false,
                'message'      => 'No API key.',
                'transactions' => array(),
            );
        }

        $cid        = (int) $contact_id;
        $take_after = max(0, min(200, (int) $limit));

        $orders_url = $this->base_url . '/orders?' . http_build_query(
            array(
                'contact_id' => $cid,
                'order_by'   => 'creation_date',
                'limit'      => 200,
            ),
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        $resp_orders = $this->http_get(
            $orders_url,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($resp_orders)) {
            return array(
                'success'      => false,
                'message'      => $resp_orders->get_error_message(),
                'transactions' => array(),
            );
        }

        $code_o = (int) wp_remote_retrieve_response_code($resp_orders);
        if ($code_o < 200 || $code_o >= 300) {
            return array(
                'success'      => false,
                'message'      => 'Orders HTTP ' . (string) $code_o,
                'transactions' => array(),
            );
        }

        $body_o = json_decode(wp_remote_retrieve_body($resp_orders), true);
        $orders = array();
        if (is_array($body_o)) {
            if (!empty($body_o['orders']) && is_array($body_o['orders'])) {
                $orders = $body_o['orders'];
            } elseif (!empty($body_o['data']) && is_array($body_o['data'])) {
                $orders = $body_o['data'];
            }
        }

        $rows = array();
        foreach ($orders as $order) {
            if (!is_array($order)) {
                continue;
            }
            $raw_date   = (string) ($order['order_date'] ?? $order['creation_date'] ?? $order['date_created'] ?? '');
            $ts         = $raw_date !== '' ? strtotime($raw_date) : false;
            $status_raw = (string) ($order['status'] ?? '');
            $rows[]     = array(
                'order_id'       => (int) ($order['id'] ?? 0),
                'date_display'   => $ts ? date('F j, Y', $ts) : '—',
                'date_raw'       => $raw_date,
                'amount_display' => '$' . number_format((float) ($order['total_paid'] ?? 0), 2),
                'title'          => (string) ($order['title'] ?? ''),
                'type'           => (string) ($order['order_type'] ?? ''),
                'status_raw'     => $status_raw,
                'status_display' => $this->format_transaction_status_display($status_raw),
            );
        }

        usort(
            $rows,
            static function ($a, $b) {
                return strcmp((string) ($b['date_raw'] ?? ''), (string) ($a['date_raw'] ?? ''));
            }
        );

        if ($take_after > 0) {
            $rows = array_slice($rows, 0, $take_after);
        }

        return array(
            'success'                  => true,
            'transactions'             => $rows,
            'keap_orders_api_response' => is_array($body_o) ? $body_o : null,
        );
    }

    /**
     * List orders in a date range with positive balance (total_due − total_paid).
     *
     * @param string $since Start date Y-m-d (optional).
     * @param string $until End date Y-m-d (optional).
     * @return list<array<string, mixed>>
     */
    public function get_balance_due_orders($since = '', $until = '') {
        if (empty($this->api_key)) {
            return array();
        }

        $params = array(
            'limit'    => 200,
            'offset'   => 0,
            'order_by' => 'order_date',
        );
        if ($since !== '') {
            $params['since_order_date'] = $since;
        }
        if ($until !== '') {
            $params['until_order_date'] = $until;
        }

        $orders_url = $this->base_url . '/orders?' . http_build_query(
            $params,
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        $response = $this->http_get(
            $orders_url,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 20,
            )
        );

        if (is_wp_error($response)) {
            return array();
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return array();
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $orders = array();
        if (is_array($body) && !empty($body['orders']) && is_array($body['orders'])) {
            $orders = $body['orders'];
        }

        $orders = array_filter(
            $orders,
            static function ($o) {
                if (!is_array($o)) {
                    return false;
                }
                $balance = (float) ($o['total_due'] ?? 0) - (float) ($o['total_paid'] ?? 0);

                return $balance > 0;
            }
        );

        return array_values($orders);
    }

    public function get_contact_tags_with_names($contact_id) {
        $response = $this->http_get(
            $this->base_url . '/contacts/' . intval($contact_id) . '/tags?limit=200',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'tags'    => array(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $tags = array();

        if (!empty($body['tags'])) {
            foreach ($body['tags'] as $row) {
                if (empty($row['tag']['id'])) {
                    continue;
                }
                $id   = (int) $row['tag']['id'];
                $name = isset($row['tag']['name']) ? (string) $row['tag']['name'] : '';
                if ($name === '') {
                    $name = 'Tag #' . $id;
                }
                $tags[] = array(
                    'id'   => $id,
                    'name' => $name,
                );
            }
        }

        return array('success' => true, 'tags' => $tags);
    }

    public function get_subscriptions($contact_id) {
        $response = $this->http_get(
            'https://api.infusionsoft.com/crm/rest/v2/subscriptions?filter=contact_id==' . intval($contact_id) . '&page_size=50',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message(), 'subscriptions' => array(), '_raw' => null);
        }

        $raw  = wp_remote_retrieve_body($response);
        $body = json_decode($raw, true);
        $subs = $body['subscriptions'] ?? array();

        // Fetch product name for each subscription so we can match by name
        foreach ($subs as &$sub) {
            $product_id = $sub['product_id'] ?? 0;
            if ($product_id) {
                $sub['_product_name'] = $this->get_product_name($product_id);
            } else {
                $sub['_product_name'] = '';
            }
        }
        unset($sub);

        return array(
            'success'       => true,
            'subscriptions' => $subs,
            '_raw'          => $body,
        );
    }

    public function cancel_subscription($subscription_id, $contact_id) {
        $sub_id = intval($subscription_id);
        $v2_url = 'https://api.infusionsoft.com/crm/rest/v2/subscriptions/' . $sub_id;

        $headers = array(
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        );

        $deactivate = $this->http_post(
            $v2_url . ':deactivate',
            array(
                'headers' => $headers,
                'body'    => wp_json_encode(array('reason' => 'Cancelled within SJE - ' . date('Y-m-d'))),
                'timeout' => 15,
            )
        );

        $parts = array();

        if (is_wp_error($deactivate)) {
            return array(
                'success' => false,
                'message' => 'Deactivate: ' . $deactivate->get_error_message(),
            );
        }

        $deactivate_code = wp_remote_retrieve_response_code($deactivate);
        $deactivate_body = wp_remote_retrieve_body($deactivate);
        $deactivate_ok   = ($deactivate_code >= 200 && $deactivate_code < 300);

        $parts[] = 'Deactivate: HTTP ' . $deactivate_code . (
            !$deactivate_ok && $deactivate_body !== '' ? ' — ' . $deactivate_body : ''
        );

        if (!$deactivate_ok) {
            return array(
                'success' => false,
                'message' => implode(' / ', $parts),
            );
        }

        $patch = $this->http_request(
            $v2_url . '?update_mask=end_date,auto_charge',
            array(
                'method'  => 'PATCH',
                'headers' => $headers,
                'body'    => wp_json_encode(array(
                    'contact_id'  => (string) intval($contact_id),
                    'end_date'    => date('Y-m-d', strtotime('-1 day')),
                    'auto_charge' => false,
                )),
                'timeout' => 15,
            )
        );

        if (is_wp_error($patch)) {
            $parts[] = 'PATCH end_date+auto_charge: ' . $patch->get_error_message();

            return array(
                'success' => true,
                'message' => implode(' / ', $parts),
            );
        }

        $patch_code = wp_remote_retrieve_response_code($patch);
        $patch_body = wp_remote_retrieve_body($patch);
        $patch_ok   = ($patch_code >= 200 && $patch_code < 300);

        $parts[] = 'PATCH end_date+auto_charge: HTTP ' . $patch_code . (
            !$patch_ok && $patch_body !== '' ? ' — ' . $patch_body : ''
        );

        return array(
            'success' => true,
            'message' => implode(' / ', $parts),
        );
    }

    public function disable_payment_plan_autocharge($contact_id) {
        // Get all orders for the contact
        $response = $this->http_get(
            $this->base_url . '/orders?contact_id=' . intval($contact_id) . '&limit=50',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message(), 'updated' => 0);
        }

        $body   = json_decode(wp_remote_retrieve_body($response), true);
        $orders = $body['orders'] ?? array();

        if (empty($orders)) {
            return array('success' => true, 'message' => 'No orders found.', 'updated' => 0);
        }

        $updated = 0;
        $skipped = 0;
        $failed  = array();

        foreach ($orders as $order) {
            $order_id = $order['id'] ?? 0;
            $pay_plan = $order['payment_plan'] ?? array();

            if (!$order_id || empty($pay_plan) || empty($pay_plan['auto_charge'])) {
                $skipped++;
                continue;
            }

            // PUT requires plan_start_date, number_of_payments, days_between_payments
            $payload = array(
                'auto_charge'           => false,
                'plan_start_date'       => $pay_plan['plan_start_date']       ?? date('Y-m-d'),
                'number_of_payments'    => $pay_plan['number_of_payments']    ?? 1,
                'days_between_payments' => $pay_plan['days_between_payments'] ?? 30,
            );

            $put = $this->http_request(
                $this->base_url . '/orders/' . intval($order_id) . '/paymentPlan',
                array(
                    'method'  => 'PUT',
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $this->api_key,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ),
                    'body'    => wp_json_encode($payload),
                    'timeout' => 15,
                )
            );

            if (is_wp_error($put)) {
                $failed[] = 'Order ' . $order_id . ': ' . $put->get_error_message();
            } else {
                $code = wp_remote_retrieve_response_code($put);
                if ($code >= 200 && $code < 300) {
                    $updated++;
                } else {
                    $failed[] = 'Order ' . $order_id . ': HTTP ' . $code . ' — ' . wp_remote_retrieve_body($put);
                }
            }
        }

        $success = empty($failed);
        $message = $updated . ' payment plan(s) auto charge disabled';
        if ($skipped > 0) {
            $message .= ', ' . $skipped . ' skipped (no auto charge)';
        }
        if (!empty($failed)) {
            $message .= '. Errors: ' . implode('; ', $failed);
        }

        return array('success' => $success, 'message' => $message, 'updated' => $updated);
    }

    public function get_contact_debug_data($contact_id) {
        $out = array();

        // 1. V2 Subscriptions for contact
        $r = $this->http_get(
            'https://api.infusionsoft.com/crm/rest/v2/subscriptions?filter=contact_id==' . intval($contact_id) . '&page_size=50',
            array(
                'headers' => array('Authorization' => 'Bearer ' . $this->api_key, 'Accept' => 'application/json'),
                'timeout' => 15,
            )
        );
        $out['v2_subscriptions'] = is_wp_error($r)
            ? array('error' => $r->get_error_message())
            : json_decode(wp_remote_retrieve_body($r), true);

        // 2. V1 Subscriptions for contact
        $r = $this->http_get(
            $this->base_url . '/subscriptions?contact_id=' . intval($contact_id) . '&limit=50',
            array(
                'headers' => array('Authorization' => 'Bearer ' . $this->api_key, 'Accept' => 'application/json'),
                'timeout' => 15,
            )
        );
        $out['v1_subscriptions'] = is_wp_error($r)
            ? array('error' => $r->get_error_message())
            : json_decode(wp_remote_retrieve_body($r), true);

        // 3. V1 Orders with payment plans
        $r = $this->http_get(
            $this->base_url . '/orders?contact_id=' . intval($contact_id) . '&limit=50',
            array(
                'headers' => array('Authorization' => 'Bearer ' . $this->api_key, 'Accept' => 'application/json'),
                'timeout' => 15,
            )
        );
        $out['v1_orders'] = is_wp_error($r)
            ? array('error' => $r->get_error_message())
            : json_decode(wp_remote_retrieve_body($r), true);

        // 4. V2 Subscription Plans (full catalogue)
        $r = $this->http_get(
            'https://api.infusionsoft.com/crm/rest/v2/subscriptionPlans?page_size=100',
            array(
                'headers' => array('Authorization' => 'Bearer ' . $this->api_key, 'Accept' => 'application/json'),
                'timeout' => 15,
            )
        );
        $out['v2_subscription_plans'] = is_wp_error($r)
            ? array('error' => $r->get_error_message())
            : json_decode(wp_remote_retrieve_body($r), true);

        return $out;
    }

    private function get_product_name($product_id) {
        $pid   = (int) $product_id;
        $tkey  = 'jecm_keap_product_' . $pid;
        $cached = get_transient($tkey);
        if (false !== $cached) {
            return (string) $cached;
        }

        $response = $this->http_get(
            $this->base_url . '/products/' . $pid,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 10,
            )
        );

        if (is_wp_error($response)) {
            return '';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $name = strtoupper((string) ($body['product_name'] ?? $body['name'] ?? ''));
        set_transient($tkey, $name, 24 * HOUR_IN_SECONDS);

        return $name;
    }

    public function remove_tags($contact_id, $tag_ids) {
        if (empty($tag_ids)) {
            return array('success' => true, 'message' => 'No tags to remove.');
        }

        $failed  = array();
        $removed = array();

        foreach ($tag_ids as $tag_id) {
            $response = $this->http_request(
                $this->base_url . '/contacts/' . intval($contact_id) . '/tags/' . intval($tag_id),
                array(
                    'method'  => 'DELETE',
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $this->api_key,
                        'Accept'        => 'application/json',
                    ),
                    'timeout' => 15,
                )
            );

            if (is_wp_error($response)) {
                $failed[] = $tag_id . ' (' . $response->get_error_message() . ')';
            } else {
                $code = wp_remote_retrieve_response_code($response);
                if ($code >= 200 && $code < 300) {
                    $removed[] = $tag_id;
                } else {
                    $failed[] = $tag_id . ' (HTTP ' . $code . ')';
                }
            }
        }

        $success = empty($failed);
        $msg     = '';
        if (!empty($removed)) {
            $msg .= count($removed) . ' removed: ' . implode(', ', $removed) . '. ';
        }
        if (!empty($failed)) {
            $msg .= 'Failed: ' . implode(', ', $failed);
        }

        return array('success' => $success, 'message' => trim($msg));
    }

    public function update_custom_fields($contact_id, $fields) {
        // Get field name → numeric ID map from model
        $model = $this->get_field_model();

        $custom_fields_payload = array();
        $not_found             = array();

        foreach ($fields as $field_name => $value) {
            $lookup_key = strtolower(ltrim($field_name, '_'));
            if (isset($model[$lookup_key])) {
                $custom_fields_payload[] = array(
                    'id'      => $model[$lookup_key],
                    'content' => $value,
                );
            } else {
                $not_found[] = $field_name;
            }
        }

        if (empty($custom_fields_payload)) {
            return array('success' => false, 'message' => 'No matching fields found in Keap model. Not found: ' . implode(', ', $not_found));
        }

        $patch_response = $this->http_request(
            $this->base_url . '/contacts/' . intval($contact_id),
            array(
                'method'  => 'PATCH',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ),
                'body'    => wp_json_encode(array('custom_fields' => $custom_fields_payload)),
                'timeout' => 15,
            )
        );

        if (is_wp_error($patch_response)) {
            return array('success' => false, 'message' => 'PATCH failed: ' . $patch_response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($patch_response);
        $body = wp_remote_retrieve_body($patch_response);
        return array(
            'success' => ($code >= 200 && $code < 300),
            'message' => 'HTTP ' . $code . ($code >= 400 ? ' — ' . $body : ''),
        );
    }

    private function get_field_model() {
        if ($this->field_model_cache !== null) {
            return $this->field_model_cache;
        }

        $cached = get_transient('jecm_keap_field_model');
        if (false !== $cached && is_array($cached)) {
            $this->field_model_cache = $cached;

            return $this->field_model_cache;
        }

        $response = $this->http_get(
            $this->base_url . '/contacts/model',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            $this->field_model_cache = array();

            return $this->field_model_cache;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $map  = array(); // field_name (lowercase, no underscore) => numeric id

        if (!empty($body['custom_fields'])) {
            foreach ($body['custom_fields'] as $cf) {
                if (!empty($cf['field_name']) && isset($cf['id'])) {
                    $key       = strtolower(ltrim($cf['field_name'], '_'));
                    $map[$key] = $cf['id'];
                }
            }
        }

        $this->field_model_cache = $map;
        set_transient('jecm_keap_field_model', $map, 24 * HOUR_IN_SECONDS);

        return $this->field_model_cache;
    }

    public function get_custom_field($contact_id, $field_name) {
        // Get field name → numeric ID map from model
        $model      = $this->get_field_model();
        $lookup_key = strtolower(ltrim($field_name, '_'));

        if (!isset($model[$lookup_key])) {
            return array('success' => false, 'message' => 'Field "' . $field_name . '" not found in Keap contact model.');
        }

        $numeric_id = $model[$lookup_key];

        // GET the contact with custom fields
        $response = $this->http_get(
            $this->base_url . '/contacts/' . intval($contact_id) . '?optional_properties=custom_fields',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept'        => 'application/json',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        foreach ($body['custom_fields'] ?? array() as $cf) {
            if ((int) $cf['id'] === (int) $numeric_id) {
                $raw     = $cf['content'] ?? '';
                $ts      = strtotime($raw);
                $display = ($ts && strlen($raw) > 6) ? date('F j, Y', $ts) : ($raw ?: '(empty)');
                return array('success' => true, 'raw' => $raw, 'display' => $display);
            }
        }

        return array('success' => false, 'message' => 'Field found in model (id:' . $numeric_id . ') but not returned on contact.');
    }
}
