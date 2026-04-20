<?php
/**
 * Admin UI + batch sender for FluentCart → JECM webhook.
 */
if (!defined('ABSPATH')) {
    exit;
}

final class JECM_Academy_Backfill {

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_post_jecm_ab_save', array($this, 'handle_save'));
        add_action('admin_post_jecm_ab_run_batch', array($this, 'handle_run_batch'));
        add_action('admin_post_jecm_ab_reset_cursor', array($this, 'handle_reset_cursor'));
    }

    public function admin_menu() {
        add_management_page(
            __('JECM → SJE order backfill', 'jecm-academy-backfill'),
            __('JECM SJE Backfill', 'jecm-academy-backfill'),
            'manage_options',
            'jecm-academy-backfill',
            array($this, 'render_page')
        );
    }

    public function handle_save() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'jecm-academy-backfill'));
        }
        check_admin_referer('jecm_ab_save', 'jecm_ab_nonce');

        update_option('jecm_ab_webhook_url', esc_url_raw(wp_unslash($_POST['jecm_ab_webhook_url'] ?? ''), array('https', 'http')));
        update_option('jecm_ab_webhook_secret', sanitize_text_field(wp_unslash($_POST['jecm_ab_webhook_secret'] ?? '')));
        update_option('jecm_ab_batch_size', max(1, min(200, absint($_POST['jecm_ab_batch_size'] ?? 25))));
        update_option('jecm_ab_delay_ms', max(0, min(5000, absint($_POST['jecm_ab_delay_ms'] ?? 100))));
        $status = sanitize_text_field(wp_unslash($_POST['jecm_ab_status'] ?? ''));
        update_option('jecm_ab_order_status', $status);

        wp_safe_redirect(
            add_query_arg(
                array('page' => 'jecm-academy-backfill', 'updated' => '1'),
                admin_url('tools.php')
            )
        );
        exit;
    }

    public function handle_reset_cursor() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'jecm-academy-backfill'));
        }
        check_admin_referer('jecm_ab_reset', 'jecm_ab_reset_nonce');
        update_option('jecm_ab_last_order_id', 0);
        wp_safe_redirect(
            add_query_arg(
                array('page' => 'jecm-academy-backfill', 'reset' => '1'),
                admin_url('tools.php')
            )
        );
        exit;
    }

    public function handle_run_batch() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'jecm-academy-backfill'));
        }
        check_admin_referer('jecm_ab_run_batch', 'jecm_ab_run_nonce');

        $result = $this->run_batch_internal();
        set_transient(
            'jecm_ab_last_result_' . get_current_user_id(),
            $result,
            120
        );

        wp_safe_redirect(
            add_query_arg(
                array('page' => 'jecm-academy-backfill', 'ran' => '1'),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * @return array{ok: bool, messages: list<string>, processed: int, cursor_after: int}
     */
    private function run_batch_internal() {
        $messages   = array();
        $url        = (string) get_option('jecm_ab_webhook_url', '');
        $secret     = (string) get_option('jecm_ab_webhook_secret', '');
        $batch      = (int) get_option('jecm_ab_batch_size', 25);
        $delay_ms   = (int) get_option('jecm_ab_delay_ms', 100);
        $status_f   = (string) get_option('jecm_ab_order_status', '');
        $cursor     = (int) get_option('jecm_ab_last_order_id', 0);

        if ($url === '') {
            return array(
                'ok'           => false,
                'messages'     => array(__('Set the webhook URL first.', 'jecm-academy-backfill')),
                'processed'    => 0,
                'cursor_after' => $cursor,
            );
        }
        if ($secret === '') {
            return array(
                'ok'           => false,
                'messages'     => array(__('Set the shared secret (must match Keap Manager → Webhook tab).', 'jecm-academy-backfill')),
                'processed'    => 0,
                'cursor_after' => $cursor,
            );
        }
        if (!class_exists('FluentCart\App\Models\Order')) {
            return array(
                'ok'           => false,
                'messages'     => array(__('FluentCart is not loaded (FluentCart\App\Models\Order missing).', 'jecm-academy-backfill')),
                'processed'    => 0,
                'cursor_after' => $cursor,
            );
        }

        /** @var \FluentCart\Framework\Database\Orm\Builder $q */
        $q = \FluentCart\App\Models\Order::query()
            ->with('customer')
            ->where('id', '>', $cursor)
            ->orderBy('id', 'asc')
            ->limit($batch);

        if ($status_f !== '') {
            $q->where('status', $status_f);
        }

        $orders = $q->get();
        if (is_object($orders) && method_exists($orders, 'all')) {
            $order_list = $orders->all();
        } elseif (is_array($orders)) {
            $order_list = $orders;
        } else {
            $order_list = iterator_to_array($orders);
        }
        if (empty($order_list)) {
            return array(
                'ok'           => true,
                'finished'     => true,
                'messages'     => array(
                    sprintf(
                        /* translators: %d: last processed order id */
                        __('No more orders after ID %d (with current filters).', 'jecm-academy-backfill'),
                        $cursor
                    ),
                ),
                'processed'    => 0,
                'cursor_after' => $cursor,
            );
        }

        $processed = 0;
        $last_id   = $cursor;

        foreach ($order_list as $order) {
            /** @var \FluentCart\App\Models\Order $order */
            $body    = $this->build_payload($order);
            $json    = wp_json_encode($body);
            if ($json === false) {
                $messages[] = sprintf(
                    /* translators: %d: order id */
                    __('Order %d: could not encode JSON.', 'jecm-academy-backfill'),
                    (int) $order->id
                );
                break;
            }

            $response = wp_remote_post(
                $url,
                array(
                    'timeout' => 30,
                    'headers' => array(
                        'Content-Type'           => 'application/json',
                        'X-JECM-Webhook-Secret'  => $secret,
                    ),
                    'body'    => $json,
                )
            );

            if (is_wp_error($response)) {
                $messages[] = sprintf(
                    /* translators: 1: order id, 2: error message */
                    __('Order %1$d: request failed — %2$s', 'jecm-academy-backfill'),
                    (int) $order->id,
                    $response->get_error_message()
                );
                break;
            }

            $code = (int) wp_remote_retrieve_response_code($response);
            if ($code < 200 || $code >= 300) {
                $snippet = wp_remote_retrieve_body($response);
                if (strlen($snippet) > 300) {
                    $snippet = substr($snippet, 0, 300) . '…';
                }
                $messages[] = sprintf(
                    /* translators: 1: order id, 2: HTTP code, 3: body snippet */
                    __('Order %1$d: HTTP %2$d — %3$s', 'jecm-academy-backfill'),
                    (int) $order->id,
                    $code,
                    $snippet
                );
                break;
            }

            $last_id = (int) $order->id;
            update_option('jecm_ab_last_order_id', $last_id);
            $processed++;

            $messages[] = sprintf(
                /* translators: %d: order id */
                __('Sent order %d OK.', 'jecm-academy-backfill'),
                (int) $order->id
            );

            if ($delay_ms > 0) {
                usleep($delay_ms * 1000);
            }
        }

        return array(
            'ok'           => $processed > 0,
            'messages'     => $messages,
            'processed'    => $processed,
            'cursor_after' => (int) get_option('jecm_ab_last_order_id', $cursor),
        );
    }

    /**
     * Shape matches what JECM_FluentCart_Webhook::locate_order_and_customer expects (top-level order + customer).
     *
     * @param \FluentCart\App\Models\Order $order
     * @return array<string, mixed>
     */
    private function build_payload($order) {
        $orderRow = $this->model_to_plain_array($order);

        $customerRow = null;
        if ($order->customer) {
            $customerRow = $this->model_to_plain_array($order->customer);
        }

        return array(
            'source'   => 'jecm_academy_backfill',
            'order'    => $orderRow,
            'customer' => $customerRow,
        );
    }

    /**
     * @param object $model FluentCart model
     * @return array<string, mixed>
     */
    private function model_to_plain_array($model) {
        $attrs = $model->getAttributes();
        $out   = array();
        foreach ($attrs as $k => $v) {
            if (is_object($v) && method_exists($v, 'format')) {
                $out[$k] = $v->format('Y-m-d H:i:s');
                continue;
            }
            if ($k === 'config' && $model instanceof \FluentCart\App\Models\Order) {
                $out[$k] = $model->config;
                continue;
            }
            if (is_string($v) && $k === 'purchase_value') {
                $decoded = json_decode($v, true);
                $out[$k] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $v;
                continue;
            }
            $out[$k] = $v;
        }
        return $out;
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $url      = (string) get_option('jecm_ab_webhook_url', '');
        $secret   = (string) get_option('jecm_ab_webhook_secret', '');
        $batch    = (int) get_option('jecm_ab_batch_size', 25);
        $delay_ms = (int) get_option('jecm_ab_delay_ms', 100);
        $status   = (string) get_option('jecm_ab_order_status', '');
        $cursor   = (int) get_option('jecm_ab_last_order_id', 0);

        $fc_ok = class_exists('FluentCart\App\Models\Order');

        $result = get_transient('jecm_ab_last_result_' . get_current_user_id());
        if ($result !== false) {
            delete_transient('jecm_ab_last_result_' . get_current_user_id());
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('JECM → SJE order backfill', 'jecm-academy-backfill'); ?></h1>

            <?php if (!$fc_ok) : ?>
                <div class="notice notice-error"><p><?php esc_html_e('FluentCart must be active. This tool loads FluentCart order models.', 'jecm-academy-backfill'); ?></p></div>
            <?php endif; ?>

            <?php if (!empty($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.', 'jecm-academy-backfill'); ?></p></div>
            <?php endif; ?>
            <?php if (!empty($_GET['reset'])) : ?>
                <div class="notice notice-info is-dismissible"><p><?php esc_html_e('Cursor reset to 0. The next batch starts from the oldest orders again (respecting status filter).', 'jecm-academy-backfill'); ?></p></div>
            <?php endif; ?>

            <?php if (!empty($_GET['ran']) && is_array($result)) : ?>
                <div class="notice <?php echo !empty($result['finished']) ? 'notice-info' : (!empty($result['ok']) ? 'notice-success' : 'notice-warning'); ?>"><p>
                    <?php if (!empty($result['finished'])) : ?>
                        <?php echo esc_html((string) ($result['messages'][0] ?? __('Nothing to do.', 'jecm-academy-backfill'))); ?>
                    <?php else : ?>
                        <?php
                        printf(
                            /* translators: 1: number sent, 2: cursor value */
                            esc_html__('Processed this run: %1$d — cursor now at order ID %2$d.', 'jecm-academy-backfill'),
                            (int) ($result['processed'] ?? 0),
                            (int) ($result['cursor_after'] ?? $cursor)
                        );
                        ?>
                    <?php endif; ?>
                </p>
                    <?php if (!empty($result['messages']) && is_array($result['messages'])) : ?>
                        <ul style="margin-left:18px;list-style:disc;">
                            <?php foreach ($result['messages'] as $line) : ?>
                                <li><?php echo esc_html((string) $line); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <p class="description">
                <?php esc_html_e('POSTs each order in small batches to your Support site webhook (same JSON shape as FluentCart “All Data” style: order + customer). Run Tools → batches until you see “No more orders”.', 'jecm-academy-backfill'); ?>
            </p>

            <hr>

            <h2><?php esc_html_e('Settings', 'jecm-academy-backfill'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:920px;">
                <?php wp_nonce_field('jecm_ab_save', 'jecm_ab_nonce'); ?>
                <input type="hidden" name="action" value="jecm_ab_save">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="jecm_ab_webhook_url"><?php esc_html_e('Webhook URL', 'jecm-academy-backfill'); ?></label></th>
                        <td>
                            <input type="url" name="jecm_ab_webhook_url" id="jecm_ab_webhook_url" class="large-text code"
                                   value="<?php echo esc_attr($url); ?>"
                                   placeholder="https://support.example.com/wp-json/jecm/v1/fluentcart">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="jecm_ab_webhook_secret"><?php esc_html_e('Shared secret', 'jecm-academy-backfill'); ?></label></th>
                        <td>
                            <input type="text" name="jecm_ab_webhook_secret" id="jecm_ab_webhook_secret" class="large-text code"
                                   value="<?php echo esc_attr($secret); ?>" autocomplete="off">
                            <p class="description"><?php esc_html_e('Same value as Keap Manager → Webhook tab. Sent as X-JECM-Webhook-Secret.', 'jecm-academy-backfill'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="jecm_ab_batch_size"><?php esc_html_e('Batch size', 'jecm-academy-backfill'); ?></label></th>
                        <td>
                            <input type="number" name="jecm_ab_batch_size" id="jecm_ab_batch_size" min="1" max="200" value="<?php echo (int) $batch; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="jecm_ab_delay_ms"><?php esc_html_e('Delay between requests (ms)', 'jecm-academy-backfill'); ?></label></th>
                        <td>
                            <input type="number" name="jecm_ab_delay_ms" id="jecm_ab_delay_ms" min="0" max="5000" value="<?php echo (int) $delay_ms; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="jecm_ab_status"><?php esc_html_e('Order status filter', 'jecm-academy-backfill'); ?></label></th>
                        <td>
                            <input type="text" name="jecm_ab_status" id="jecm_ab_status" class="regular-text"
                                   value="<?php echo esc_attr($status); ?>"
                                   placeholder="completed">
                            <p class="description"><?php esc_html_e('Leave empty for all statuses. Example: completed', 'jecm-academy-backfill'); ?></p>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save settings', 'jecm-academy-backfill'); ?></button>
                </p>
            </form>

            <hr>

            <h2><?php esc_html_e('Run batch', 'jecm-academy-backfill'); ?></h2>
            <p>
                <?php
                printf(
                    /* translators: %d: last successfully sent order id */
                    esc_html__('Cursor (last successfully sent order ID): %d', 'jecm-academy-backfill'),
                    $cursor
                );
                ?>
            </p>
            <p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                    <?php wp_nonce_field('jecm_ab_run_batch', 'jecm_ab_run_nonce'); ?>
                    <input type="hidden" name="action" value="jecm_ab_run_batch">
                    <button type="submit" class="button button-primary" <?php disabled(!$fc_ok || $url === '' || $secret === ''); ?>>
                        <?php esc_html_e('Send next batch', 'jecm-academy-backfill'); ?>
                    </button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline; margin-left:8px;">
                    <?php wp_nonce_field('jecm_ab_reset', 'jecm_ab_reset_nonce'); ?>
                    <input type="hidden" name="action" value="jecm_ab_reset_cursor">
                    <button type="submit" class="button button-secondary"
                            onclick="return confirm('<?php echo esc_js(__('Reset cursor to 0? You may re-send duplicate orders to SJE.', 'jecm-academy-backfill')); ?>');">
                        <?php esc_html_e('Reset cursor', 'jecm-academy-backfill'); ?>
                    </button>
                </form>
            </p>
        </div>
        <?php
    }
}
