<?php
/**
 * Plugin Name: FC Helper
 * Description: Generate styled HTML product descriptions for FluentCart using AI (Katahdin AI Hub).
 * Version: 1.0.2
 * Author: Jazzedge
 * License: GPL v2 or later
 * Text Domain: fc-helper
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FC_HELPER_VERSION', '1.0.2');
define('FC_HELPER_PLUGIN_FILE', __FILE__);
define('FC_HELPER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FC_HELPER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FC_HELPER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Whether Katahdin AI Hub is loaded and available.
 *
 * @return bool
 */
function fc_helper_is_hub_active() {
    return function_exists('katahdin_ai_hub')
        && class_exists('Katahdin_AI_Hub')
        && katahdin_ai_hub()
        && katahdin_ai_hub()->api_manager;
}

/**
 * Inline Heroicons (outline, 24×24) scaled via width/height attributes.
 *
 * @param string               $name Icon key: tag, play-circle, gift, pencil-square, swatch, sparkles, arrow-path, clipboard, clipboard-document, document-duplicate, check, document-text, magnifying-glass.
 * @param array<string, string> $attr Optional HTML attributes.
 * @return string
 */
function fc_helper_icon($name, $attr = array()) {
    $defaults = array(
        'class'  => 'fc-helper-svg-icon',
        'width'  => '16',
        'height' => '16',
    );
    $attr = array_merge($defaults, $attr);
    $a    = '';
    foreach ($attr as $k => $v) {
        $a .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
    }
    $paths = array(
        'tag'                  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>',
        'play-circle'           => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z"/>',
        'gift'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m-8.25 3.75h16.5a1.125 1.125 0 00.375-2.184M6.75 9h-.375a1.125 1.125 0 00-.375 2.184m13.5 0h.375a1.125 1.125 0 00.375-2.184M9.75 9v.375a1.125 1.125 0 01-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V9m6 3.75v-.375a1.125 1.125 0 00-1.125-1.125h-2.25a1.125 1.125 0 00-1.125 1.125v.375"/>',
        'pencil-square'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/>',
        'document-duplicate'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/>',
        'swatch'               => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z"/>',
        'sparkles'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>',
        'arrow-path'           => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-2.198M7.227 15.453A8.25 8.25 0 019.183 2.523m-1.89 3.184a8.25 8.25 0 0013.803 2.198l3.181-3.183m0-4.991v4.99"/>',
        'clipboard'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>',
        'clipboard-document'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.056 1.123-.064M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.064M15.75 18v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-1.5a2.251 2.251 0 00-2.152 1.526m0 0A2.25 2.25 0 013 5.25v9.75a2.25 2.25 0 002.25 2.25H15a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.064M15.75 18v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H5.25"/>',
        'check'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>',
        'document-text'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
        'magnifying-glass'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>',
    );
    if (!isset($paths[ $name ])) {
        return '';
    }

    return sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"%1$s>%2$s</svg>',
        $a,
        $paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed path markup only.
    );
}

require_once FC_HELPER_PLUGIN_DIR . 'includes/class-ai-generator.php';
require_once FC_HELPER_PLUGIN_DIR . 'includes/class-admin-page.php';

add_action('plugins_loaded', function () {
    load_plugin_textdomain('fc-helper', false, dirname(FC_HELPER_PLUGIN_BASENAME) . '/languages');

    FC_Helper_Admin_Page::instance();
    add_action('admin_notices', 'fc_helper_hub_missing_notice');
});

/**
 * Register with Katahdin AI Hub for quota/usage tracking.
 */
add_action('katahdin_ai_hub_init', function ($hub) {
    if (!is_object($hub) || !method_exists($hub, 'register_plugin')) {
        return;
    }
    $hub->register_plugin(
        'fc-helper',
        array(
            'name'        => 'FC Helper',
            'version'     => FC_HELPER_VERSION,
            'quota_limit' => 100000,
            'description' => 'FluentCart product HTML generator',
        )
    );
});

add_action(
    'wp',
    function () {
        if (is_singular('fluent-products')) {
            remove_filter('the_content', 'wpautop');
            remove_filter('the_content', 'wpautop_fix');
            remove_filter('the_content', 'wptexturize');
        }
    }
);

/**
 * Admin notice when hub is not active.
 */
function fc_helper_hub_missing_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (fc_helper_is_hub_active()) {
        return;
    }
    echo '<div class="notice notice-error"><p>' . esc_html__('FC Helper requires the Katahdin AI Hub plugin to be active.', 'fc-helper') . '</p></div>';
}
