<?php
/**
 * Shortcode rendering and frontend AJAX.
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Academy Starter landing page shortcode.
 */
class AcademyStarterShortcode {
	const SHORTCODE = 'academy_starter_landing';
	const AJAX_ACTION = 'academy_starter_log_click';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_log_click' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_log_click' ) );
		add_action( 'wp_head', array( __CLASS__, 'inject_ga4' ), 1 );
	}

	/**
	 * Render shortcode output.
	 *
	 * @return string
	 */
	public static function render() {
		$style = get_option( AcademyStarterAdmin::OPTION_STYLE, 'jazz_piano' );
		$data  = AcademyStarterStyles::get_style_data( $style );

		$data['style_key']    = sanitize_key( $style );
		$data['paid_url']     = get_option( AcademyStarterAdmin::OPTION_PAID_URL, AcademyStarterAdmin::DEFAULT_PAID_URL );
		$data['free_form_id'] = (int) get_option( AcademyStarterAdmin::OPTION_FREE_FORM_ID, 0 );

		AcademyStarterAnalytics::log_view( $data['style_key'] );
		self::enqueue_assets( $data['style_key'] );

		ob_start();

		$template = ACADEMY_STARTER_PLUGIN_DIR . 'templates/landing-page.php';
		if ( is_readable( $template ) ) {
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @param string $style Style key.
	 * @return void
	 */
	private static function enqueue_assets( $style ) {
		$css_path = ACADEMY_STARTER_PLUGIN_DIR . 'assets/css/frontend.css';
		$js_path  = ACADEMY_STARTER_PLUGIN_DIR . 'assets/js/frontend.js';

		wp_enqueue_style(
			'academy-starter-frontend',
			ACADEMY_STARTER_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			is_readable( $css_path ) ? (string) filemtime( $css_path ) : ACADEMY_STARTER_VERSION
		);

		wp_enqueue_script(
			'academy-starter-frontend',
			ACADEMY_STARTER_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			is_readable( $js_path ) ? (string) filemtime( $js_path ) : ACADEMY_STARTER_VERSION,
			true
		);

		wp_localize_script(
			'academy-starter-frontend',
			'AcademyStarter',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::AJAX_ACTION ),
				'action'  => self::AJAX_ACTION,
				'style'   => sanitize_key( $style ),
				'ga4Id'   => get_option( AcademyStarterAdmin::OPTION_GA4_ID, '' ),
			)
		);
	}

	/**
	 * Inject GA4 gtag.js snippet if a Measurement ID is configured.
	 *
	 * @return void
	 */
	public static function inject_ga4() {
		$ga4_id = get_option( AcademyStarterAdmin::OPTION_GA4_ID, '' );

		if ( empty( $ga4_id ) ) {
			return;
		}
		?>
		<!-- Academy Starter GA4 -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4_id ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', '<?php echo esc_js( $ga4_id ); ?>');
		</script>
		<?php
	}

	/**
	 * AJAX click logging handler.
	 *
	 * @return void
	 */
	public static function ajax_log_click() {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		$style = isset( $_POST['style'] ) ? sanitize_key( wp_unslash( $_POST['style'] ) ) : get_option( AcademyStarterAdmin::OPTION_STYLE, 'jazz_piano' );
		$type  = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';

		$logged = AcademyStarterAnalytics::log_click( $style, $type );

		if ( ! $logged ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid click type.', 'academy-starter' ),
				),
				400
			);
		}

		wp_send_json_success();
	}
}
