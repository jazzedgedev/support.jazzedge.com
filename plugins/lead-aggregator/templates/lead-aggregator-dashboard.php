<?php
/**
 * Template Name: Lead Aggregator Dashboard
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    $login_page_id = (int) get_option('lead_aggregator_login_page_id', 0);
    $login_url = $login_page_id ? get_permalink($login_page_id) : home_url('/login');
    wp_redirect($login_url);
    exit;
}

$app_mode = get_option('lead_aggregator_app_mode', true);

if (!$app_mode) {
    get_header();
    ?>
    <main class="lead-aggregator-template-wrap">
        <?php echo do_shortcode('[lead_aggregator_dashboard]'); ?>
    </main>
    <?php
    get_footer();
    return;
}

$menu_id = (int) get_option('lead_aggregator_app_menu_id', 0);
$logo_id = (int) get_option('lead_aggregator_app_logo_id', 0);
$footer_enabled = (int) get_option('lead_aggregator_app_footer_enabled', 0);
$footer_text = get_option('lead_aggregator_app_footer_text', '');

$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('lead-aggregator-app'); ?>>
    <div class="la-app-shell">
        <header class="la-app-header">
            <div class="la-app-brand">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="la-app-logo">
                <?php else : ?>
                    <span class="la-app-title"><?php echo esc_html(get_bloginfo('name')); ?></span>
                <?php endif; ?>
            </div>
            <nav class="la-app-nav">
                <?php
                if ($menu_id) {
                    wp_nav_menu(array(
                        'menu' => $menu_id,
                        'container' => false,
                        'menu_class' => 'la-app-menu',
                        'fallback_cb' => '__return_empty_string',
                    ));
                }
                ?>
            </nav>
        </header>
        <main class="lead-aggregator-template-wrap">
            <?php echo do_shortcode('[lead_aggregator_dashboard]'); ?>
        </main>
        <?php if ($footer_enabled) : ?>
            <footer class="la-app-footer">
                <?php echo wp_kses_post($footer_text); ?>
            </footer>
        <?php endif; ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
