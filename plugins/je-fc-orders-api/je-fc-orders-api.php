<?php
/**
 * Plugin Name: JE FC Orders API
 * Description: Authenticated REST endpoint for FluentCart order totals by date range.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const JE_FC_ORDERS_API_KEY_OPTION = 'je_fc_orders_api_key';
const JE_FC_ORDERS_API_REQUEST_LOG_OPTION = 'je_fc_orders_api_request_log';

add_action('rest_api_init', static function () {
    $range_args = array(
        'year'          => array(
            'required'          => true,
            'sanitize_callback' => 'absint',
        ),
        'from_month'    => array(
            'required'          => true,
            'sanitize_callback' => 'absint',
        ),
        'through_month' => array(
            'required'          => true,
            'sanitize_callback' => 'absint',
        ),
    );

    register_rest_route(
        'jazzedge/v1',
        '/fc-orders',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'je_fc_orders_api_handle_request',
            'permission_callback' => 'je_fc_orders_api_check_key',
            'args'                => $range_args,
        )
    );

    register_rest_route(
        'jazzedge/v1',
        '/fc-orders-list',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'je_fc_orders_api_handle_list_request',
            'permission_callback' => 'je_fc_orders_api_check_key',
            'args'                => $range_args,
        )
    );
});

/**
 * @param WP_REST_Request $request
 * @return true|WP_Error
 */
function je_fc_orders_api_check_key($request) {
    // API key: wp-admin > FC Orders API, or wp_options (`je_fc_orders_api_key`).
    $stored = get_option(JE_FC_ORDERS_API_KEY_OPTION, '');
    $header = $request->get_header('X-JE-API-Key');

    if (!is_string($header) || $header === '') {
        return new WP_Error(
            'rest_forbidden',
            __('Invalid or missing API key.'),
            array('status' => 401)
        );
    }

    if (!is_string($stored) || $stored === '' || !hash_equals((string) $stored, $header)) {
        return new WP_Error(
            'rest_forbidden',
            __('Invalid or missing API key.'),
            array('status' => 401)
        );
    }

    return true;
}

/**
 * Validate shared date-range query params.
 *
 * @param WP_REST_Request $request
 * @return array{year:int, from_month:int, through_month:int}|WP_Error
 */
function je_fc_orders_api_validate_range_params($request) {
    $year          = absint($request->get_param('year'));
    $from_month    = absint($request->get_param('from_month'));
    $through_month = absint($request->get_param('through_month'));

    if ($year < 1) {
        return new WP_Error(
            'rest_invalid_param',
            __('Invalid year.'),
            array('status' => 400)
        );
    }

    if ($from_month < 1 || $from_month > 12 || $through_month < 1 || $through_month > 12) {
        return new WP_Error(
            'rest_invalid_param',
            __('Months must be between 1 and 12.'),
            array('status' => 400)
        );
    }

    if ($from_month > $through_month) {
        return new WP_Error(
            'rest_invalid_param',
            __('from_month must be less than or equal to through_month.'),
            array('status' => 400)
        );
    }

    return array(
        'year'          => $year,
        'from_month'    => $from_month,
        'through_month' => $through_month,
    );
}

/**
 * Append a request log entry, keeping only the most recent 20.
 *
 * @param string       $endpoint
 * @param array        $params
 * @param int          $http_code
 * @param array|string $response
 */
function je_fc_orders_api_append_request_log($endpoint, $params, $http_code, $response) {
    $log = get_option(JE_FC_ORDERS_API_REQUEST_LOG_OPTION, array());
    if (!is_array($log)) {
        $log = array();
    }

    $log[] = array(
        'time'      => current_time('mysql'),
        'endpoint'  => $endpoint,
        'params'    => array(
            'year'          => isset($params['year']) ? (int) $params['year'] : 0,
            'from_month'    => isset($params['from_month']) ? (int) $params['from_month'] : 0,
            'through_month' => isset($params['through_month']) ? (int) $params['through_month'] : 0,
        ),
        'http_code' => (int) $http_code,
        'response'  => $response,
    );

    if (count($log) > 20) {
        $log = array_slice($log, -20);
    }

    update_option(JE_FC_ORDERS_API_REQUEST_LOG_OPTION, $log, false);
}

/**
 * Get live completed order totals from FluentCart orders.
 *
 * @param int $year
 * @param int $month
 * @param int|null $through_month
 * @return array{orders:int, revenue:float}
 */
function je_fc_orders_api_get_live_totals($year, $month, $through_month = null) {
    global $wpdb;

    $year          = absint($year);
    $month         = absint($month);
    $through_month = $through_month === null ? $month : absint($through_month);

    if ($year < 1 || $month < 1 || $month > 12 || $through_month < 1 || $through_month > 12 || $month > $through_month) {
        return array(
            'orders'  => 0,
            'revenue' => 0.0,
        );
    }

    $table = $wpdb->prefix . 'fct_orders';

    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return array(
            'orders'  => 0,
            'revenue' => 0.0,
        );
    }

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS total FROM {$table}
            WHERE status = %s AND YEAR(completed_at) = %d
            AND MONTH(completed_at) BETWEEN %d AND %d",
            'completed',
            $year,
            $month,
            $through_month
        ),
        ARRAY_A
    );

    if ($row === null) {
        return array(
            'orders'  => 0,
            'revenue' => 0.0,
        );
    }

    $orders      = isset($row['orders']) ? (int) $row['orders'] : 0;
    $total_cents = isset($row['total']) ? (float) $row['total'] : 0.0;

    return array(
        'orders'  => $orders,
        'revenue' => $total_cents / 100.0,
    );
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function je_fc_orders_api_handle_request($request) {
    global $wpdb;

    $validated = je_fc_orders_api_validate_range_params($request);
    if (is_wp_error($validated)) {
        return $validated;
    }
    $year          = $validated['year'];
    $from_month    = $validated['from_month'];
    $through_month = $validated['through_month'];

    $table = $wpdb->prefix . 'fct_orders';

    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        $response = array(
            'revenue' => 0.0,
            'orders'  => 0,
        );

        je_fc_orders_api_append_request_log('fc-orders', $validated, 200, $response);

        $rest_response = new WP_REST_Response($response, 200);
        $rest_response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $rest_response->header('Pragma', 'no-cache');

        return $rest_response;
    }

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS total FROM {$table}
            WHERE status = %s AND YEAR(completed_at) = %d
            AND MONTH(completed_at) BETWEEN %d AND %d",
            'completed',
            $year,
            $from_month,
            $through_month
        ),
        ARRAY_A
    );

    if ($row === null) {
        $message = __('Could not read order data.');

        je_fc_orders_api_append_request_log('fc-orders', $validated, 500, $message);

        return new WP_Error(
            'rest_query_failed',
            $message,
            array('status' => 500)
        );
    }

    $orders = isset($row['orders']) ? (int) $row['orders'] : 0;
    $total_cents = isset($row['total']) ? (float) $row['total'] : 0.0;
    $revenue     = $total_cents / 100.0;

    $response = array(
        'revenue' => $revenue,
        'orders'  => $orders,
    );

    je_fc_orders_api_append_request_log('fc-orders', $validated, 200, $response);

    $rest_response = new WP_REST_Response($response, 200);
    $rest_response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    $rest_response->header('Pragma', 'no-cache');

    return $rest_response;
}

/**
 * List completed orders in range (for reconciliation / reporting).
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function je_fc_orders_api_handle_list_request($request) {
    global $wpdb;

    $validated = je_fc_orders_api_validate_range_params($request);
    if (is_wp_error($validated)) {
        return $validated;
    }
    $year          = $validated['year'];
    $from_month    = $validated['from_month'];
    $through_month = $validated['through_month'];

    $table = $wpdb->prefix . 'fct_orders';

    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        $response = array();

        je_fc_orders_api_append_request_log('fc-orders-list', $validated, 200, $response);

        $rest_response = new WP_REST_Response($response, 200);
        $rest_response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $rest_response->header('Pragma', 'no-cache');

        return $rest_response;
    }

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, completed_at, total_amount, billing_first_name, billing_last_name
            FROM {$table}
            WHERE status = %s AND YEAR(completed_at) = %d
            AND MONTH(completed_at) BETWEEN %d AND %d
            ORDER BY completed_at ASC",
            'completed',
            $year,
            $from_month,
            $through_month
        ),
        ARRAY_A
    );

    if ($rows === null) {
        $message = __('Could not read order data.');

        je_fc_orders_api_append_request_log('fc-orders-list', $validated, 500, $message);

        return new WP_Error(
            'rest_query_failed',
            $message,
            array('status' => 500)
        );
    }

    $out = array();
    foreach ($rows as $row) {
        $first = isset($row['billing_first_name']) ? trim((string) $row['billing_first_name']) : '';
        $last  = isset($row['billing_last_name']) ? trim((string) $row['billing_last_name']) : '';
        $name  = trim($first . ' ' . $last);

        $completed = isset($row['completed_at']) ? $row['completed_at'] : '';
        $date_str  = '';
        if ($completed !== '') {
            $ts = strtotime($completed);
            if ($ts) {
                $date_str = wp_date('Y-m-d', $ts);
            }
        }

        $cents  = isset($row['total_amount']) ? (float) $row['total_amount'] : 0.0;
        $amount = round($cents / 100.0, 2);

        $out[] = array(
            'id'     => isset($row['id']) ? (int) $row['id'] : 0,
            'date'   => $date_str,
            'name'   => $name,
            'amount' => $amount,
        );
    }

    je_fc_orders_api_append_request_log('fc-orders-list', $validated, 200, $out);

    $rest_response = new WP_REST_Response($out, 200);
    $rest_response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    $rest_response->header('Pragma', 'no-cache');

    return $rest_response;
}

add_action('admin_menu', 'je_fc_orders_api_register_menu');
add_action('admin_init', 'je_fc_orders_api_register_settings');
add_action('admin_post_je_fc_orders_api_regenerate', 'je_fc_orders_api_handle_regenerate');
add_action('admin_post_je_fc_orders_api_clear_log', 'je_fc_orders_api_handle_clear_log');
add_action('admin_notices', 'je_fc_orders_api_admin_notices');

/**
 * Top-level settings page (manage_options).
 */
function je_fc_orders_api_register_menu() {
    add_menu_page(
        __('FC Orders API', 'je-fc-orders-api'),
        __('FC Orders API', 'je-fc-orders-api'),
        'manage_options',
        'je-fc-orders-api',
        'je_fc_orders_api_render_settings_page',
        'dashicons-rest-api',
        81
    );
}

/**
 * Settings API registration.
 */
function je_fc_orders_api_register_settings() {
    register_setting(
        'je_fc_orders_api_group',
        JE_FC_ORDERS_API_KEY_OPTION,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'je_fc_orders_api_sanitize_api_key',
            'default'           => '',
        )
    );

    add_settings_section(
        'je_fc_orders_api_main',
        __('API settings', 'je-fc-orders-api'),
        '__return_false',
        'je_fc_orders_api'
    );

    add_settings_field(
        'je_fc_orders_api_key_field',
        __('API Key', 'je-fc-orders-api'),
        'je_fc_orders_api_render_api_key_field',
        'je_fc_orders_api',
        'je_fc_orders_api_main'
    );

    add_settings_field(
        'je_fc_orders_api_endpoint_field',
        __('Endpoint URL', 'je-fc-orders-api'),
        'je_fc_orders_api_render_endpoint_field',
        'je_fc_orders_api',
        'je_fc_orders_api_main'
    );
}

/**
 * Preserve existing key when the form still shows the masked placeholder or an empty "unchanged" submit.
 *
 * @param mixed $value Raw option value from POST.
 * @return string
 */
function je_fc_orders_api_sanitize_api_key($value) {
    $previous = get_option(JE_FC_ORDERS_API_KEY_OPTION, '');
    if (!is_string($value)) {
        return is_string($previous) ? $previous : '';
    }
    $value = trim($value);
    if ($value === '' || $value === '********') {
        return is_string($previous) ? $previous : '';
    }
    return sanitize_text_field($value);
}

/**
 * Regenerate key (nonce + admin_post).
 */
function je_fc_orders_api_handle_regenerate() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to do this.', 'je-fc-orders-api'));
    }
    check_admin_referer('je_fc_orders_api_regenerate');

    $new_key = wp_generate_password(32, false);
    update_option(JE_FC_ORDERS_API_KEY_OPTION, $new_key);

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'         => 'je-fc-orders-api',
                'regenerated'  => '1',
            ),
            admin_url('admin.php')
        )
    );
    exit;
}

/**
 * Clear request log (nonce + admin_post).
 */
function je_fc_orders_api_handle_clear_log() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to do this.', 'je-fc-orders-api'));
    }
    check_admin_referer('je_fc_orders_api_clear_log');

    delete_option(JE_FC_ORDERS_API_REQUEST_LOG_OPTION);

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'    => 'je-fc-orders-api',
                'cleared' => '1',
            ),
            admin_url('admin.php')
        )
    );
    exit;
}

/**
 * Admin notice after regenerate.
 */
function je_fc_orders_api_admin_notices() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'je-fc-orders-api') {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_GET['regenerated']) && $_GET['regenerated'] === '1') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('API key regenerated.', 'je-fc-orders-api') . '</p></div>';
    }
    if (isset($_GET['cleared']) && $_GET['cleared'] === '1') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Request log cleared.', 'je-fc-orders-api') . '</p></div>';
    }
}

/**
 * API Key field + Regenerate + reveal JS.
 */
function je_fc_orders_api_render_api_key_field() {
    $stored = get_option(JE_FC_ORDERS_API_KEY_OPTION, '');
    $has_key = is_string($stored) && $stored !== '';
    ?>
    <div
        class="je-fc-api-key-wrap"
        <?php
        if ($has_key) {
            echo ' data-api-key="' . esc_attr($stored) . '"';
        }
        ?>
    >
        <input
            type="password"
            name="je_fc_orders_api_key"
            id="je_fc_orders_api_key"
            class="regular-text"
            value="<?php echo $has_key ? '********' : ''; ?>"
            autocomplete="off"
            <?php echo $has_key ? 'readonly' : ''; ?>
        />
        <button type="button" class="button" id="je_fc_orders_api_reveal_toggle" <?php echo $has_key ? '' : ' style="display:none"'; ?>>
            <?php esc_html_e('Reveal', 'je-fc-orders-api'); ?>
        </button>
        <button
            type="submit"
            class="button"
            style="margin-left:6px;vertical-align:middle;"
            form="je_fc_orders_api_regenerate_form"
        ><?php esc_html_e('Regenerate', 'je-fc-orders-api'); ?></button>
    </div>
    <script>
    (function () {
        var wrap = document.querySelector('.je-fc-api-key-wrap');
        if (!wrap) {
            return;
        }
        var form = wrap.closest('form');
        var input = document.getElementById('je_fc_orders_api_key');
        var btn = document.getElementById('je_fc_orders_api_reveal_toggle');
        var secret = wrap.getAttribute('data-api-key') || '';
        var masked = true;

        if (secret) {
            btn.style.display = '';
        }

        if (btn && secret) {
            btn.addEventListener('click', function () {
                masked = !masked;
                if (masked) {
                    input.setAttribute('type', 'password');
                    input.value = '********';
                    input.readOnly = true;
                    btn.textContent = '<?php echo esc_js(__('Reveal', 'je-fc-orders-api')); ?>';
                } else {
                    input.setAttribute('type', 'text');
                    input.value = secret;
                    input.readOnly = false;
                    btn.textContent = '<?php echo esc_js(__('Hide', 'je-fc-orders-api')); ?>';
                    input.focus();
                }
            });
            input.addEventListener('input', function () {
                if (!masked) {
                    secret = input.value;
                    wrap.setAttribute('data-api-key', secret);
                }
            });
        }

        if (form && input) {
            form.addEventListener('submit', function () {
                if (input.value === '********' && secret) {
                    input.removeAttribute('name');
                    var h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = '<?php echo esc_js(JE_FC_ORDERS_API_KEY_OPTION); ?>';
                    h.value = secret;
                    form.appendChild(h);
                }
            });
        }
    })();
    </script>
    <?php
}

/**
 * Read-only endpoint URL for copying.
 */
function je_fc_orders_api_render_endpoint_field() {
    $url = home_url('/wp-json/jazzedge/v1/fc-orders');
    ?>
    <input type="text" class="large-text code" readonly value="<?php echo esc_attr($url); ?>" onclick="this.select();" />
    <?php
}

/**
 * Render live order totals from local FluentCart data.
 */
function je_fc_orders_api_render_live_totals() {
    $now_ts           = current_time('timestamp');
    $current_year     = (int) wp_date('Y', $now_ts);
    $current_month    = (int) wp_date('n', $now_ts);
    $last_month_ts    = strtotime('-1 month', $now_ts);
    $last_month_year  = (int) wp_date('Y', $last_month_ts);
    $last_month_month = (int) wp_date('n', $last_month_ts);

    $periods = array(
        array(
            'label'  => __('This Month', 'je-fc-orders-api'),
            'totals' => je_fc_orders_api_get_live_totals($current_year, $current_month),
        ),
        array(
            'label'  => __('Last Month', 'je-fc-orders-api'),
            'totals' => je_fc_orders_api_get_live_totals($last_month_year, $last_month_month),
        ),
        array(
            'label'  => __('Year to Date', 'je-fc-orders-api'),
            'totals' => je_fc_orders_api_get_live_totals($current_year, 1, $current_month),
        ),
    );
    ?>
    <h2><?php esc_html_e('Live Order Totals', 'je-fc-orders-api'); ?></h2>
    <table class="widefat" style="max-width:500px;margin-bottom:20px;">
        <thead>
            <tr>
                <th><?php esc_html_e('Period', 'je-fc-orders-api'); ?></th>
                <th><?php esc_html_e('Orders', 'je-fc-orders-api'); ?></th>
                <th><?php esc_html_e('Revenue', 'je-fc-orders-api'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($periods as $period) : ?>
                <tr>
                    <td><?php echo esc_html($period['label']); ?></td>
                    <td><strong><?php echo esc_html((string) $period['totals']['orders']); ?></strong></td>
                    <td><strong>$<?php echo esc_html(number_format($period['totals']['revenue'], 2)); ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

/**
 * Render recent request log.
 */
function je_fc_orders_api_render_request_log() {
    $log = get_option(JE_FC_ORDERS_API_REQUEST_LOG_OPTION, array());
    if (!is_array($log)) {
        $log = array();
    }
    ?>
    <h2><?php esc_html_e('Recent Requests', 'je-fc-orders-api'); ?></h2>
    <?php if (empty($log)) : ?>
        <p><?php esc_html_e('No requests logged yet.', 'je-fc-orders-api'); ?></p>
    <?php else : ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Time', 'je-fc-orders-api'); ?></th>
                    <th><?php esc_html_e('Endpoint', 'je-fc-orders-api'); ?></th>
                    <th><?php esc_html_e('Params', 'je-fc-orders-api'); ?></th>
                    <th><?php esc_html_e('HTTP', 'je-fc-orders-api'); ?></th>
                    <th><?php esc_html_e('Response', 'je-fc-orders-api'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($log) as $entry) : ?>
                    <?php
                    $params_json   = wp_json_encode(isset($entry['params']) ? $entry['params'] : array(), JSON_PRETTY_PRINT);
                    $response_json = wp_json_encode(isset($entry['response']) ? $entry['response'] : '', JSON_PRETTY_PRINT);
                    if (!is_string($params_json)) {
                        $params_json = '';
                    }
                    if (!is_string($response_json)) {
                        $response_json = '';
                    }
                    if (strlen($response_json) > 200) {
                        $response_json = substr($response_json, 0, 200) . '...';
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html(isset($entry['time']) ? (string) $entry['time'] : ''); ?></td>
                        <td><?php echo esc_html(isset($entry['endpoint']) ? (string) $entry['endpoint'] : ''); ?></td>
                        <td><pre><code><?php echo esc_html($params_json); ?></code></pre></td>
                        <td><?php echo esc_html(isset($entry['http_code']) ? (string) (int) $entry['http_code'] : ''); ?></td>
                        <td><pre><code><?php echo esc_html($response_json); ?></code></pre></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('je_fc_orders_api_clear_log'); ?>
            <input type="hidden" name="action" value="je_fc_orders_api_clear_log" />
            <?php submit_button(__('Clear Log', 'je-fc-orders-api'), 'secondary', 'submit', false); ?>
        </form>
    <?php endif; ?>
    <?php
}

/**
 * Settings page markup.
 */
function je_fc_orders_api_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php settings_errors(); ?>
        <?php je_fc_orders_api_render_live_totals(); ?>
        <form id="je_fc_orders_api_regenerate_form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="screen-reader-text">
            <?php wp_nonce_field('je_fc_orders_api_regenerate'); ?>
            <input type="hidden" name="action" value="je_fc_orders_api_regenerate" />
        </form>
        <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="post">
            <?php
            settings_fields('je_fc_orders_api_group');
            do_settings_sections('je_fc_orders_api');
            submit_button(__('Save Changes', 'je-fc-orders-api'));
            ?>
        </form>
        <?php je_fc_orders_api_render_request_log(); ?>
    </div>
    <?php
}
