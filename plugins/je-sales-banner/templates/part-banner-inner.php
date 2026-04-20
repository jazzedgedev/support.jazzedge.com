<?php
/**
 * Compact single-bar layout (desktop one row; mobile max 2 rows).
 *
 * @var object $row
 * @var string $sale_label
 * @var string $coupon_code
 * @var string $coupon_highlight
 * @var string $context
 */

if (!defined('ABSPATH')) {
    exit;
}

$coupon_fg = JE_SB_Frontend::contrast_text_color($coupon_highlight);
$coupon_style = 'background-color:' . esc_attr($coupon_highlight) . ';color:' . esc_attr($coupon_fg);
$has_coupon = $coupon_code !== '';
$je_sb_group_flex = 'display:flex!important;flex-direction:row!important;align-items:center!important;';
?>
<div class="je-sale-banner__inner<?php echo $has_coupon ? '' : ' je-sale-banner__inner--no-coupon'; ?>" style="<?php echo esc_attr('display:flex!important;flex-direction:row!important;align-items:center!important;padding:0 12px!important;width:100%!important;'); ?>">
    <div class="je-sale-banner__bar" style="<?php echo esc_attr('display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:center!important;gap:10px!important;width:100%!important;min-height:52px;padding:6px 0!important;'); ?>">
        <div class="je-sale-banner__group je-sale-banner__group--lead" style="<?php echo esc_attr($je_sb_group_flex); ?>">
            <span class="je-sale-banner__badge"><?php esc_html_e('SALE', 'je-sales-banner'); ?></span>
            <span class="je-sale-banner__headline" style="<?php echo esc_attr('overflow:hidden;white-space:nowrap;text-overflow:ellipsis;flex:1 1 auto;min-width:0;'); ?>">
                <span class="je-sale-banner__title"><?php echo esc_html((string) $row->title); ?></span>
                <?php if (!empty($row->description)) : ?>
                    <span class="je-sale-banner__dot" aria-hidden="true">·</span>
                    <span class="je-sale-banner__desc"><?php echo esc_html((string) $row->description); ?></span>
                <?php endif; ?>
            </span>
        </div>

        <?php if ($has_coupon) : ?>
            <span class="je-sale-banner__divider" aria-hidden="true"></span>
            <div class="je-sale-banner__group je-sale-banner__group--coupon" style="<?php echo esc_attr($je_sb_group_flex); ?>">
                <span class="je-sale-banner__coupon" style="<?php echo esc_attr($coupon_style); ?>">
                    <span class="je-sale-banner__coupon-label"><?php esc_html_e('Use Code:', 'je-sales-banner'); ?></span>
                    <span class="je-sale-banner__coupon-code" data-je-sb-code><?php echo esc_html(strtoupper($coupon_code)); ?></span>
                    <button type="button" class="je-sale-banner__coupon-copy" data-je-sb-copy="<?php echo esc_attr($coupon_code); ?>" aria-label="<?php esc_attr_e('Copy coupon code', 'je-sales-banner'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        <span class="je-sale-banner__coupon-tip" data-je-sb-tip hidden><?php esc_html_e('Copied!', 'je-sales-banner'); ?></span>
                    </button>
                </span>
            </div>
        <?php endif; ?>

        <span class="je-sale-banner__divider" aria-hidden="true"></span>

        <div class="je-sale-banner__group je-sale-banner__group--tail" style="<?php echo esc_attr($je_sb_group_flex); ?>">
            <span class="je-sale-banner__timer" data-je-sb-timer aria-live="polite">
                <span data-je-sb-timer-value></span>
            </span>
            <a class="je-sale-banner__cta" href="<?php echo esc_url((string) $row->cta_url); ?>"><?php echo esc_html((string) $row->cta_label); ?></a>
            <span class="je-sale-banner__amount je-sale-banner__amount--desktop"><?php echo wp_kses_post($sale_label); ?></span>
        </div>
    </div>
</div>
