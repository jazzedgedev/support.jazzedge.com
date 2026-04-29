<?php
/**
 * Admin menu and shared assets.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers top-level menu and enqueues assets on plugin screens.
 */
class APB_Sides_Admin {

	public function apb_sides_register_menu(): void {
		add_menu_page(
			__( 'APB Sides Database', 'apb-sides-database' ),
			__( 'APB Sides Database', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-dashboard',
			array( new APB_Sides_Admin_Dashboard(), 'render' ),
			'dashicons-media-document',
			58
		);

		add_submenu_page(
			'apb-sides-dashboard',
			__( 'Dashboard', 'apb-sides-database' ),
			__( 'Dashboard', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-dashboard',
			array( new APB_Sides_Admin_Dashboard(), 'render' )
		);

		add_submenu_page(
			'apb-sides-dashboard',
			__( 'Uploads', 'apb-sides-database' ),
			__( 'Uploads', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-uploads',
			array( new APB_Sides_Admin_Uploads(), 'render' )
		);

		add_submenu_page(
			'apb-sides-dashboard',
			__( 'Scripts', 'apb-sides-database' ),
			__( 'Scripts', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-scripts',
			array( new APB_Sides_Admin_Scripts(), 'render' )
		);

		add_submenu_page(
			'apb-sides-dashboard',
			__( 'Characters', 'apb-sides-database' ),
			__( 'Characters', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-characters',
			array( new APB_Sides_Admin_Characters(), 'render' )
		);

		add_submenu_page(
			'apb-sides-dashboard',
			__( 'Sides', 'apb-sides-database' ),
			__( 'Sides', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-sides',
			array( new APB_Sides_Admin_Sides(), 'render' )
		);

		add_submenu_page(
			'apb-sides-dashboard',
			__( 'Settings', 'apb-sides-database' ),
			__( 'Settings', 'apb-sides-database' ),
			'manage_options',
			'apb-sides-settings',
			array( new APB_Sides_Admin_Settings(), 'render_page' )
		);
	}

	/**
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function apb_sides_enqueue_assets( string $hook_suffix ): void {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$is_apb_screen = ( strpos( $hook_suffix, 'apb-sides' ) !== false )
			|| ( $page !== '' && str_starts_with( $page, 'apb-sides' ) );

		if ( ! $is_apb_screen ) {
			return;
		}

		$apb_css_path = APB_SIDES_PLUGIN_DIR . 'assets/css/admin.css';
		$apb_js_path  = APB_SIDES_PLUGIN_DIR . 'assets/js/admin.js';
		$apb_css_ver  = file_exists( $apb_css_path ) ? (string) filemtime( $apb_css_path ) : APB_SIDES_VERSION;
		$apb_js_ver   = file_exists( $apb_js_path ) ? (string) filemtime( $apb_js_path ) : APB_SIDES_VERSION;

		wp_enqueue_style(
			'apb-sides-admin',
			APB_SIDES_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$apb_css_ver
		);

		wp_enqueue_script(
			'apb-sides-admin',
			APB_SIDES_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$apb_js_ver,
			true
		);

		$apb_l10n = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'apb_sides_admin' ),
		);

		wp_localize_script(
			'apb-sides-admin',
			'apbSidesAdmin',
			$apb_l10n
		);

		/*
		 * Fallback if localization is stripped or reordered (matches Katahdin AI Hub pattern of
		 * relying on ajaxurl + ensuring admin AJAX config exists).
		 */
		wp_add_inline_script(
			'apb-sides-admin',
			'(function(){var c=' . wp_json_encode( $apb_l10n ) . ';window.apbSidesAdmin=window.apbSidesAdmin||{};window.apbSidesAdmin.ajaxUrl=window.apbSidesAdmin.ajaxUrl||c.ajaxUrl;window.apbSidesAdmin.nonce=window.apbSidesAdmin.nonce||c.nonce;})();',
			'after'
		);
	}
}
