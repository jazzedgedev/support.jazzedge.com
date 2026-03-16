<?php
/**
 * JEM Marketing - Opt-in Form Template
 *
 * @package Jazzedge_Marketing
 * @var object $funnel Funnel object
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="jem-optin-wrapper" style="max-width:480px;margin:40px auto;background:#fff;padding:40px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.12);">
    <h2><?php esc_html_e('Get Your Free Sheet Music!', 'jazzedge-marketing'); ?></h2>
    <p class="jem-song-title">🎵 <?php echo esc_html( $funnel->name ); ?></p>
    <p class="jem-subheading"><?php esc_html_e('Enter your info below and download on the next page.', 'jazzedge-marketing'); ?></p>

    <form id="jem-optin-form" class="jem-optin-form" method="post" data-funnel-id="<?php echo esc_attr($funnel->id); ?>">
        <div class="jem-honeypot-field" style="display:none!important;visibility:hidden!important;position:absolute!important;left:-9999px!important;" aria-hidden="true">
            <input type="text" name="jem_hp" value="" tabindex="-1" autocomplete="off">
        </div>
        <input type="hidden" name="invite_code" value="<?php echo esc_attr($funnel->invite_code); ?>">

        <div class="jem-form-group">
            <label for="jem_first_name" style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#444;margin-bottom:4px;"><?php esc_html_e('First Name', 'jazzedge-marketing'); ?></label>
            <input type="text" id="jem_first_name" name="first_name" required placeholder="<?php esc_attr_e('Your first name', 'jazzedge-marketing'); ?>" style="display:block;width:100%;padding:12px 16px;font-size:15px;border:1.5px solid #ddd;border-radius:5px;box-sizing:border-box;margin-top:4px;">
        </div>
        <div class="jem-form-group">
            <label for="jem_last_name" style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#444;margin-bottom:4px;"><?php esc_html_e('Last Name', 'jazzedge-marketing'); ?></label>
            <input type="text" id="jem_last_name" name="last_name" required placeholder="<?php esc_attr_e('Your last name', 'jazzedge-marketing'); ?>" style="display:block;width:100%;padding:12px 16px;font-size:15px;border:1.5px solid #ddd;border-radius:5px;box-sizing:border-box;margin-top:4px;">
        </div>
        <div class="jem-form-group">
            <label for="jem_email" style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#444;margin-bottom:4px;"><?php esc_html_e('Email Address', 'jazzedge-marketing'); ?></label>
            <input type="email" id="jem_email" name="email" required placeholder="<?php esc_attr_e('you@example.com', 'jazzedge-marketing'); ?>" style="display:block;width:100%;padding:12px 16px;font-size:15px;border:1.5px solid #ddd;border-radius:5px;box-sizing:border-box;margin-top:4px;">
        </div>

        <button type="submit" class="jem-submit-btn" style="display:block;width:100%;padding:14px;background:#0a7c7c;color:#fff;font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:1px;border:none;border-radius:5px;cursor:pointer;margin-top:8px;"><?php esc_html_e('Get Free Access →', 'jazzedge-marketing'); ?></button>
        <p class="jem-privacy-note" style="font-size:12px;color:#999;text-align:center;margin-top:14px;"><?php esc_html_e('🔒 We respect your privacy. No spam, ever.', 'jazzedge-marketing'); ?></p>
    </form>

    <div id="jem-form-message" class="jem-message" style="display:none;"></div>
</div>
