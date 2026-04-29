<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

/**
 * Stylized WordPress login page.
 */
add_action( 'login_enqueue_scripts', function () {
	$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : ( wp_get_theme()->get( 'Version' ) ?: '1.0' );
	wp_enqueue_style(
		'custom-login',
		get_stylesheet_directory_uri() . '/login.css',
		array( 'login' ),
		$version
	);
} );

add_filter( 'login_headerurl', function () {
	return home_url();
} );

add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );

/**
 * Hide admin bar for non-administrators.
 */
add_filter( 'show_admin_bar', function () {
	return current_user_can( 'manage_options' );
} );

/**
 * Block non-administrators from wp-admin.
 */
add_action( 'admin_init', function () {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_safe_redirect( home_url() );
		exit;
	}
} );

/**
 * APB Homepage template: assets, layout, default loop, and opt-in handler.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_page_template( 'template-apb-homepage.php' ) ) {
		wp_enqueue_style(
			'apb-homepage',
			get_stylesheet_directory_uri() . '/apb-homepage.css',
			array(),
			'1.0'
		);
	}
} );

add_filter( 'body_class', function ( $classes ) {
	if ( is_page_template( 'template-apb-homepage.php' ) ) {
		$classes[] = 'apb-homepage-template';
	}
	return $classes;
} );

add_action( 'wp', function () {
	if ( ! is_page_template( 'template-apb-homepage.php' ) ) {
		return;
	}
	add_filter(
		'generate_sidebar_layout',
		function () {
			return 'no-sidebar';
		},
		50
	);
	add_filter( 'generate_show_entry_header', '__return_false' );
	add_filter( 'generate_show_title', '__return_false' );
} );

add_action(
	'generate_before_main_content',
	function () {
		if ( ! is_page_template( 'template-apb-homepage.php' ) ) {
			return;
		}
		add_filter( 'generate_has_default_loop', '__return_false', 99 );
	},
	1
);

/**
 * Handle Kenneth Lonergan lead form (admin-post.php).
 */
function apb_handle_lonergan_optin() {
	if ( ! isset( $_POST['apb_lonergan_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['apb_lonergan_nonce'] ) ), 'apb_lonergan_optin' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'generatepress' ), '', array( 'response' => 403 ) );
	}

	$email = isset( $_POST['apb_email'] ) ? sanitize_email( wp_unslash( $_POST['apb_email'] ) ) : '';
	$name  = isset( $_POST['apb_name'] ) ? sanitize_text_field( wp_unslash( $_POST['apb_name'] ) ) : '';

	$redirect = wp_get_referer();
	if ( ! $redirect ) {
		$redirect = home_url( '/' );
	}

	if ( empty( $email ) || ! is_email( $email ) || empty( $name ) ) {
		wp_safe_redirect( add_query_arg( 'apb_optin', 'invalid', $redirect ) );
		exit;
	}

	/**
	 * Fires after a valid APB Lonergan opt-in submission.
	 *
	 * @param string $email Submitter email.
	 * @param string $name  Submitter name.
	 */
	do_action( 'apb_lonergan_optin_submit', $email, $name );

	wp_safe_redirect( add_query_arg( 'apb_optin', 'thanks', $redirect ) );
	exit;
}

add_action( 'admin_post_nopriv_apb_lonergan_optin', 'apb_handle_lonergan_optin' );
add_action( 'admin_post_apb_lonergan_optin', 'apb_handle_lonergan_optin' );
