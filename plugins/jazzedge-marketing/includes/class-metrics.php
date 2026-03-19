<?php
/**
 * Jazzedge Marketing - Metrics Admin Page
 *
 * @package Jazzedge_Marketing
 */

if (!defined('ABSPATH')) {
    exit;
}

class JEM_Metrics {

    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'jazzedge-marketing'));
        }

        $funnels = $this->database->get_funnels();
        $metrics = array();
        foreach ($funnels as $f) {
            $optins = $this->database->count_events($f['id'], 'opt_in');
            $downloads = $this->database->count_events($f['id'], 'download_click');
            $product_clicks = $this->database->count_events($f['id'], 'purchase_click');
            $purchases_converted = $this->database->count_coupon_purchases($f['id']);
            $download_rate = $optins > 0 ? round($downloads / $optins * 100, 1) : 0;
            $product_click_rate = $optins > 0 ? round($product_clicks / $optins * 100, 1) : 0;
            $purchase_converted_rate = $optins > 0 ? round($purchases_converted / $optins * 100, 1) : 0;
            $metrics[] = array(
                'name' => $f['name'],
                'optins' => $optins,
                'downloads' => $downloads,
                'download_rate' => $download_rate,
                'product_clicks' => $product_clicks,
                'product_click_rate' => $product_click_rate,
                'purchases_converted' => $purchases_converted,
                'purchase_converted_rate' => $purchase_converted_rate,
            );
        }

        $recent_leads = $this->database->get_recent_leads(50);
        global $wpdb;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('JEM Marketing Metrics', 'jazzedge-marketing'); ?></h1>

            <h2><?php esc_html_e('Funnel Performance', 'jazzedge-marketing'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Funnel', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Opt-ins', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Downloads', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Download Rate', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Product Clicks', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Product Click Rate', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Purchases', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Purchase Rate', 'jazzedge-marketing'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($metrics)) : ?>
                        <tr><td colspan="8"><?php esc_html_e('No funnels yet.', 'jazzedge-marketing'); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($metrics as $m) : ?>
                            <tr>
                                <td><?php echo esc_html($m['name']); ?></td>
                                <td><?php echo (int) $m['optins']; ?></td>
                                <td><?php echo (int) $m['downloads']; ?></td>
                                <td><?php echo esc_html($m['download_rate'] . '%'); ?></td>
                                <td><?php echo (int) $m['product_clicks']; ?></td>
                                <td><?php echo esc_html($m['product_click_rate'] . '%'); ?></td>
                                <td><?php echo (int) $m['purchases_converted']; ?></td>
                                <td><?php echo esc_html($m['purchase_converted_rate'] . '%'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2><?php esc_html_e('Recent Leads (Last 50)', 'jazzedge-marketing'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Name', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Email', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Funnel', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Coupon Code', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Coupon Status', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Coupon Expires', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Purchased', 'jazzedge-marketing'); ?></th>
                        <th><?php esc_html_e('Webhook Sent', 'jazzedge-marketing'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_leads)) : ?>
                        <tr><td colspan="9"><?php esc_html_e('No leads yet.', 'jazzedge-marketing'); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($recent_leads as $l) : ?>
                            <?php
                            $coupon_status = '—';
                            if ( ! empty( $l['coupon_code'] ) ) {
                                $coupon = $wpdb->get_row( $wpdb->prepare(
                                    "SELECT status FROM {$wpdb->prefix}fct_coupons WHERE code = %s LIMIT 1",
                                    $l['coupon_code']
                                ) );
                                $coupon_status = $coupon ? $coupon->status : '—';
                            }
                            $coupon_status_class = ( $coupon_status === 'active' ) ? 'jem-status-active' : ( ( $coupon_status === 'expired' ) ? 'jem-status-inactive' : '' );
                            $purchased = !empty($l['coupon_use_count']) && (int) $l['coupon_use_count'] >= 1 ? '✓' : '—';
                            $webhook = !empty($l['webhook_sent']) ? 'Y' : 'N';
                            ?>
                            <tr>
                                <td><?php echo esc_html($l['created_at']); ?></td>
                                <td><?php echo esc_html($l['first_name'] . ' ' . $l['last_name']); ?></td>
                                <td><?php echo esc_html($l['email']); ?></td>
                                <td><?php echo esc_html($l['funnel_name'] ?? '-'); ?></td>
                                <td><code><?php echo esc_html($l['coupon_code']); ?></code></td>
                                <td><?php echo $coupon_status_class ? '<span class="' . esc_attr( $coupon_status_class ) . '">' . esc_html( $coupon_status ) . '</span>' : esc_html( $coupon_status ); ?></td>
                                <td><?php echo !empty($l['coupon_expires']) ? esc_html(get_date_from_gmt($l['coupon_expires'], 'Y-m-d H:i:s')) : '—'; ?></td>
                                <td><?php echo esc_html($purchased); ?></td>
                                <td><?php echo esc_html($webhook); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
