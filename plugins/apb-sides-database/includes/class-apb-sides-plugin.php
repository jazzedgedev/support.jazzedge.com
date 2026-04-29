<?php
/**
 * Core plugin singleton.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots hooks for admin, frontend, AJAX.
 */
class APB_Sides_Plugin {

	private static ?APB_Sides_Plugin $instance = null;

	private APB_Sides_Loader $loader;

	private function __construct() {
		$this->loader = new APB_Sides_Loader();
	}

	public static function instance(): APB_Sides_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		$this->loader->add_action( 'init', array( $this, 'apb_sides_register_shortcodes' ), 10, 0 );

		if ( is_admin() ) {
			$admin = new APB_Sides_Admin();
			$this->loader->add_action( 'admin_menu', array( $admin, 'apb_sides_register_menu' ), 10, 0 );
			$this->loader->add_action( 'admin_enqueue_scripts', array( $admin, 'apb_sides_enqueue_assets' ), 10, 1 );
			$settings = new APB_Sides_Admin_Settings();
			$this->loader->add_action( 'admin_init', array( $settings, 'apb_sides_register_settings' ), 10, 0 );
		}

		new APB_Sides_Ajax_Admin();
		new APB_Sides_Ajax_Frontend();

		$this->loader->run();
	}

	public function apb_sides_register_shortcodes(): void {
		$sc = new APB_Sides_Shortcodes();
		$sc->apb_sides_register();
	}
}
