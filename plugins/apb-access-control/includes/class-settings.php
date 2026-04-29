<?php
/**
 * Settings: Denied Access Redirect URL.
 *
 * @package APB_Access_Control
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_Settings
 */
class APB_Settings {

	/**
	 * Default redirect path when option is unset.
	 */
	private const DEFAULT_REDIRECT = '/join-the-actorplaybook-community/';

	/**
	 * Bootstrap hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
	}

	/**
	 * Add submenu under Settings.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_options_page(
			'APB Access',
			'APB Access',
			'manage_options',
			'apb-access',
			[ __CLASS__, 'render_page' ]
		);
	}

	/**
	 * Register option, section, and field.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'apb_access_settings',
			'apb_access_redirect_url',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_url',
				'default'           => self::DEFAULT_REDIRECT,
			]
		);

		add_settings_section(
			'apb_access_main',
			'',
			'__return_false',
			'apb-access'
		);

		add_settings_field(
			'apb_access_redirect_url',
			'Denied Access Redirect URL',
			[ __CLASS__, 'render_redirect_field' ],
			'apb-access',
			'apb_access_main'
		);
	}

	/**
	 * Output redirect URL field.
	 *
	 * @return void
	 */
	public static function render_redirect_field(): void {
		$value = get_option( 'apb_access_redirect_url', self::DEFAULT_REDIRECT );
		printf(
			'<input type="url" name="apb_access_redirect_url" id="apb_access_redirect_url" class="regular-text" value="%s" />',
			esc_attr( $value )
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'apb_access_settings' );
				do_settings_sections( 'apb-access' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
