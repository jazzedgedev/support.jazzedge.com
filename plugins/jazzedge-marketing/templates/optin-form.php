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
<div class="jem-optin-wrapper">
    <h2><?php esc_html_e('Get Your Free Sheet Music!', 'jazzedge-marketing'); ?></h2>
    <p class="jem-subheading"><?php esc_html_e('Enter your info below and we\'ll send it right over.', 'jazzedge-marketing'); ?></p>

    <form class="jem-optin-form" method="post" data-funnel-id="<?php echo esc_attr($funnel->id); ?>">
        <div class="jem-honeypot-field" aria-hidden="true">
            <input type="text" name="jem_hp" value="" tabindex="-1" autocomplete="off">
        </div>

        <div class="jem-form-group">
            <label for="jem_first_name"><?php esc_html_e('First Name', 'jazzedge-marketing'); ?></label>
            <input type="text" id="jem_first_name" name="first_name" required placeholder="<?php esc_attr_e('Your first name', 'jazzedge-marketing'); ?>">
        </div>
        <div class="jem-form-group">
            <label for="jem_last_name"><?php esc_html_e('Last Name', 'jazzedge-marketing'); ?></label>
            <input type="text" id="jem_last_name" name="last_name" required placeholder="<?php esc_attr_e('Your last name', 'jazzedge-marketing'); ?>">
        </div>
        <div class="jem-form-group">
            <label for="jem_email"><?php esc_html_e('Email Address', 'jazzedge-marketing'); ?></label>
            <input type="email" id="jem_email" name="email" required placeholder="<?php esc_attr_e('you@example.com', 'jazzedge-marketing'); ?>">
        </div>

        <button type="submit" class="jem-submit-btn"><?php esc_html_e('Get Free Access →', 'jazzedge-marketing'); ?></button>
        <p class="jem-privacy-note"><?php esc_html_e('🔒 We respect your privacy. No spam, ever.', 'jazzedge-marketing'); ?></p>
    </form>

    <div id="jem-form-message" class="jem-message" style="display:none;"></div>
</div>
