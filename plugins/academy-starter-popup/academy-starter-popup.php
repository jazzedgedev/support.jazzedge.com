<?php
/**
 * Plugin Name: Academy Starter Popup
 * Description: Shows a starter program modal and ribbon for logged-out visitors.
 * Version: 1.0.0
 * Author: Jazzedge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACADEMY_STARTER_POPUP_URL', plugin_dir_url( __FILE__ ) );
define( 'ACADEMY_STARTER_POPUP_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue assets for logged-out visitors on the front-end.
 */
function academy_starter_popup_enqueue_assets() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}

	$css_path = ACADEMY_STARTER_POPUP_PATH . 'assets/css/academy-starter-popup.css';
	$js_path  = ACADEMY_STARTER_POPUP_PATH . 'assets/js/academy-starter-popup.js';

	wp_enqueue_style(
		'academy-starter-popup',
		ACADEMY_STARTER_POPUP_URL . 'assets/css/academy-starter-popup.css',
		array(),
		file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
	);

	wp_enqueue_script(
		'academy-starter-popup',
		ACADEMY_STARTER_POPUP_URL . 'assets/js/academy-starter-popup.js',
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
		true
	);

	wp_add_inline_script(
		'academy-starter-popup',
		'window.AcademyStarterPopup = ' . wp_json_encode(
			array(
				'startUrl'     => 'https://jazzedge.academy/start',
				'dismissedKey' => 'academy_starter_popup_dismissed',
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'academy_starter_popup_enqueue_assets' );

/**
 * Render modal + ribbon in the footer for logged-out users.
 */
function academy_starter_popup_render() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}
	?>
	<div id="academy-starter-popup-overlay" class="academy-starter-popup-overlay" aria-hidden="true">
		<div class="academy-starter-popup-modal" role="dialog" aria-modal="true" aria-labelledby="academy-starter-popup-title">
			<button type="button" class="academy-starter-popup-close" aria-label="Close">&times;</button>
			<div class="academy-starter-popup-header">
				<span class="academy-starter-popup-pill">Piano in 90 Days</span>
				<h2 id="academy-starter-popup-title">Transform Your Piano Playing in 90 Days</h2>
				<p>Start free with the 30-Day Piano Playbook, or upgrade to Starter Plus for full access.</p>
			</div>
			<div class="academy-starter-popup-content">
				<ul>
					<li>Step-by-step plan that tells you exactly what to practice.</li>
					<li>Beginner-friendly lessons that build real skills fast.</li>
					<li>Playbook + Blueprints for blues, rock, and standards.</li>
				</ul>
				<div class="academy-starter-popup-cta">
					<a href="https://jazzedge.academy/start" class="academy-starter-popup-button">Get Started Free</a>
					<span class="academy-starter-popup-note">No credit card required.</span>
				</div>
			</div>
		</div>
	</div>

	<div id="academy-starter-popup-ribbon" class="academy-starter-popup-ribbon" aria-hidden="true">
		<div class="academy-starter-popup-ribbon-inner">
			<span><strong>Start Playing Piano the Right Way.</strong> Follow the 90-day Academy Starter plan.</span>
			<a href="https://jazzedge.academy/start" class="academy-starter-popup-ribbon-link">Start Free</a>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'academy_starter_popup_render' );
