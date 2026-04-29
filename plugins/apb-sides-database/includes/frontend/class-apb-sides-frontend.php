<?php
/**
 * Frontend asset registration.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and enqueues public CSS/JS when shortcodes request it.
 */
class APB_Sides_Frontend {

	private static bool $assets_enqueued = false;

	/**
	 * Call from shortcode render before output.
	 */
	public static function apb_sides_enqueue_assets(): void {
		if ( self::$assets_enqueued ) {
			return;
		}
		self::$assets_enqueued = true;

		wp_enqueue_style(
			'apb-sides-frontend',
			APB_SIDES_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			APB_SIDES_VERSION
		);

		wp_enqueue_script(
			'apb-sides-frontend',
			APB_SIDES_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			APB_SIDES_VERSION,
			true
		);

		$page_side_view = get_page_by_path( 'sides/view' );

		wp_localize_script(
			'apb-sides-frontend',
			'apbSidesFrontend',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'apb_sides_frontend' ),
				'sideDetailUrl'   => $page_side_view ? get_permalink( $page_side_view ) : home_url( '/sides/view/' ),
				'scriptDetailUrl' => get_permalink( get_page_by_path( 'scripts' ) ) ?: home_url( '/scripts/' ),
			)
		);
	}

	/**
	 * Ensure a published page with slug `scripts` and the script detail shortcode exists (admin only).
	 */
	public static function maybe_ensure_scripts_page(): void {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = get_page_by_path( 'scripts', OBJECT, 'page' );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$content = (string) $page->post_content;
			if ( false === strpos( $content, '[apb_script_detail]' ) ) {
				wp_update_post(
					array(
						'ID'           => (int) $page->ID,
						'post_content' => $content . "\n\n[apb_script_detail]",
					)
				);
			}
			return;
		}
		wp_insert_post(
			array(
				'post_title'   => __( 'Scripts', 'apb-sides-database' ),
				'post_name'    => 'scripts',
				'post_content' => '[apb_script_detail]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);
	}
}

add_action( 'admin_init', array( 'APB_Sides_Frontend', 'maybe_ensure_scripts_page' ), 1 );
