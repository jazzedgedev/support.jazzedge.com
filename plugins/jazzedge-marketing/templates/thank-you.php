<?php
/**
 * JEM Marketing - Thank You Page Template
 *
 * @package Jazzedge_Marketing
 * @var object $lead Lead object
 * @var object $funnel Funnel object
 * @var bool $expired Whether coupon has expired
 */

if (!defined('ABSPATH')) {
    exit;
}
$product_url = add_query_arg('coupon', $lead->coupon_code, $funnel->product_url);
?>
<div class="jem-thank-you">
    <section class="jem-download-section">
        <h2><?php esc_html_e('Download Your Sheet Music', 'jazzedge-marketing'); ?></h2>
        <a href="<?php echo esc_url(add_query_arg('jem_download', $lead->download_token, home_url('/'))); ?>" class="jem-download-btn"><?php esc_html_e('Download', 'jazzedge-marketing'); ?></a>
    </section>

    <section class="jem-offer-section <?php echo $expired ? 'jem-hidden' : ''; ?>">
        <h2><?php esc_html_e('Your Special Offer', 'jazzedge-marketing'); ?></h2>
        <div class="jem-coupon-box">
            <span class="jem-coupon-label"><?php esc_html_e('Coupon Code:', 'jazzedge-marketing'); ?></span>
            <code class="jem-coupon-code" id="jem-coupon-display"><?php echo esc_html($lead->coupon_code); ?></code>
            <button type="button" class="jem-copy-coupon button"><?php esc_html_e('Copy', 'jazzedge-marketing'); ?></button>
        </div>
        <div id="jem-countdown" class="jem-countdown"></div>
        <p>
            <a href="<?php echo esc_url($product_url); ?>" class="jem-purchase-btn" id="jem-purchase-btn" data-lead-id="<?php echo esc_attr($lead->id); ?>" data-funnel-id="<?php echo esc_attr($lead->funnel_id); ?>"><?php esc_html_e('Claim Your Discount', 'jazzedge-marketing'); ?></a>
        </p>
    </section>

    <section class="jem-expired-section <?php echo $expired ? '' : 'jem-hidden'; ?>">
        <p class="jem-expired-msg"><?php esc_html_e('This offer has expired.', 'jazzedge-marketing'); ?></p>
    </section>
</div>
