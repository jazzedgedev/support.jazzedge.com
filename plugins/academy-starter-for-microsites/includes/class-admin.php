<?php
/**
 * Admin settings page.
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders Academy Starter admin settings.
 */
class AcademyStarterAdmin {
	const PAGE_SLUG = 'academy-starter';
	const OPTION_STYLE = 'academy_starter_style';
	const OPTION_PAID_URL = 'academy_starter_paid_url';
	const OPTION_FREE_FORM_ID = 'academy_starter_free_form_id';
	const OPTION_GA4_ID = 'academy_starter_ga4_id';
	const OPTION_ABOUT_TEXT = 'academy_starter_about_text';
	const OPTION_HIDE_HEADER = 'academy_starter_hide_header';
	const DEFAULT_PAID_URL = 'https://ft217.infusionsoft.com/app/orderForms/starter-discount';

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_academy_starter_reset_stats', array( __CLASS__, 'handle_reset_stats' ) );
		add_action( 'admin_post_academy_starter_reset_about_text', array( __CLASS__, 'handle_reset_about_text' ) );
		add_action( 'wp_ajax_academy_starter_apply_seo', array( __CLASS__, 'handle_apply_seo' ) );
		add_action( 'wp_ajax_academy_starter_get_day_views', array( __CLASS__, 'handle_get_day_views' ) );
		add_action( 'admin_post_academy_starter_generate_api_key', array( __CLASS__, 'handle_generate_api_key' ) );
		add_action( 'wp_head', array( __CLASS__, 'maybe_hide_header' ) );
	}

	/**
	 * Register settings page under Settings.
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_options_page(
			__( 'Academy Starter', 'academy-starter' ),
			__( 'Academy Starter', 'academy-starter' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register plugin options.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			'academy_starter_settings',
			self::OPTION_STYLE,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_style' ),
				'default'           => 'jazz_piano',
			)
		);

		register_setting(
			'academy_starter_settings',
			self::OPTION_PAID_URL,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => self::DEFAULT_PAID_URL,
			)
		);

		register_setting(
			'academy_starter_settings',
			self::OPTION_FREE_FORM_ID,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);

		register_setting(
			'academy_starter_settings',
			self::OPTION_GA4_ID,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_ga4_id' ),
				'default'           => '',
			)
		);

		register_setting(
			'academy_starter_settings',
			self::OPTION_ABOUT_TEXT,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => '',
			)
		);

		register_setting(
			'academy_starter_settings',
			self::OPTION_HIDE_HEADER,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'default'           => '',
			)
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		$is_academy_starter_page = 'settings_page_' . self::PAGE_SLUG === $hook;

		if ( ! $is_academy_starter_page && ( empty( $_GET['page'] ) || self::PAGE_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) ) {
			return;
		}

		$admin_css_path = ACADEMY_STARTER_PLUGIN_DIR . 'assets/css/admin.css';
		$admin_js_path  = ACADEMY_STARTER_PLUGIN_DIR . 'assets/js/admin.js';

		wp_enqueue_style(
			'academy-starter-admin',
			ACADEMY_STARTER_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			is_readable( $admin_css_path ) ? (string) filemtime( $admin_css_path ) : ACADEMY_STARTER_VERSION
		);

		wp_enqueue_script(
			'academy-starter-admin',
			ACADEMY_STARTER_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			is_readable( $admin_js_path ) ? (string) filemtime( $admin_js_path ) : ACADEMY_STARTER_VERSION,
			true
		);

		wp_localize_script(
			'academy-starter-admin',
			'academyStarterAdmin',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'resetNonce'     => wp_create_nonce( 'academy_starter_reset_stats' ),
				'seoNonce'       => wp_create_nonce( 'academy_starter_apply_seo' ),
				'dayViewsNonce'  => wp_create_nonce( 'academy_starter_day_views' ),
				'confirmMessage' => __( 'Are you sure you want to delete all stats? This cannot be undone.', 'academy-starter' ),
			)
		);
	}

	/**
	 * AJAX handler: reset all analytics data.
	 *
	 * @return void
	 */
	public static function handle_reset_stats() {
		check_ajax_referer( 'academy_starter_reset_stats', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'academy-starter' ) ) );
		}

		AcademyStarterAnalytics::reset_stats();

		wp_send_json_success( array( 'message' => __( 'Stats have been reset.', 'academy-starter' ) ) );
	}

	public static function handle_reset_about_text() {
		check_admin_referer( 'academy_starter_reset_about' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'academy-starter' ) );
		}
		delete_option( self::OPTION_ABOUT_TEXT );
		wp_safe_redirect( admin_url( 'options-general.php?page=academy-starter&about_reset=1' ) );
		exit;
	}

	/**
	 * AJAX handler: return individual view records for a given date.
	 *
	 * @return void
	 */
	public static function handle_get_day_views() {
		check_ajax_referer( 'academy_starter_day_views', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'academy-starter' ) ) );
		}

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date.', 'academy-starter' ) ) );
		}

		$rows = AcademyStarterAnalytics::get_views_for_date( $date );
		$unique_ips = count( array_unique( array_filter( array_column( $rows, 'ip_address' ) ) ) );

		$formatted = array_map(
			static function ( $row ) {
				$ip   = ! empty( $row['ip_address'] ) ? $row['ip_address'] : __( 'Unknown', 'academy-starter' );
				$time = isset( $row['created_at'] ) ? date_i18n( 'g:i:s a', strtotime( $row['created_at'] ) ) : '—';

				return array(
					'time' => $time,
					'ip'   => $ip,
				);
			},
			$rows
		);

		wp_send_json_success(
			array(
				'date'       => $date,
				'views'      => $formatted,
				'total'      => count( $formatted ),
				'unique_ips' => $unique_ips,
			)
		);
	}

	/**
	 * AJAX handler: write SEO suggestions to homepage via AIOSEO or Yoast.
	 *
	 * @return void
	 */
	public static function handle_apply_seo() {
		check_ajax_referer( 'academy_starter_apply_seo', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'academy-starter' ) ) );
		}

		$homepage_id = (int) get_option( 'page_on_front' );

		if ( ! $homepage_id ) {
			wp_send_json_error( array( 'message' => __( 'No static homepage is set. Go to Settings → Reading first.', 'academy-starter' ) ) );
		}

		$style      = get_option( self::OPTION_STYLE, 'jazz_piano' );
		$style_data = AcademyStarterStyles::get_style_data( $style );

		$seo_title   = isset( $style_data['seo_title'] ) ? $style_data['seo_title'] : '';
		$seo_desc    = isset( $style_data['seo_description'] ) ? $style_data['seo_description'] : '';
		$focus_kw    = isset( $style_data['focus_keyword'] ) ? $style_data['focus_keyword'] : '';
		$og_desc     = isset( $style_data['og_description'] ) ? $style_data['og_description'] : '';
		$seo_excerpt = isset( $style_data['seo_excerpt'] ) ? $style_data['seo_excerpt'] : $seo_desc;

		wp_update_post(
			array(
				'ID'           => $homepage_id,
				'post_excerpt' => $seo_excerpt,
			)
		);

		$applied_via = '';

		if ( class_exists( '\AIOSEO\Plugin\Common\Models\Post' ) ) {
			try {
				$aioseo_post = \AIOSEO\Plugin\Common\Models\Post::getPost( $homepage_id );

				$aioseo_post->title          = $seo_title;
				$aioseo_post->description    = $seo_desc;
				$aioseo_post->og_title       = $seo_title;
				$aioseo_post->og_description = $og_desc;

				$keyphrases = array(
					'focus'      => array(
						'keyphrase' => $focus_kw,
						'score'     => 0,
						'analysis'  => array(),
					),
					'additional' => array(),
				);
				$aioseo_post->keyphrases = wp_json_encode( $keyphrases );

				$aioseo_post->save();
				$applied_via = 'AIOSEO';
			} catch ( \Exception $e ) {
				// Fall through to direct DB write.
			}
		}

		if ( ! $applied_via ) {
			global $wpdb;
			$aioseo_table = $wpdb->prefix . 'aioseo_posts';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $aioseo_table ) );

			if ( $table_exists === $aioseo_table ) {
				$keyphrases = wp_json_encode(
					array(
						'focus'      => array(
							'keyphrase' => $focus_kw,
							'score'     => 0,
							'analysis'  => array(),
						),
						'additional' => array(),
					)
				);

				$existing = $wpdb->get_var(
					$wpdb->prepare( "SELECT id FROM {$aioseo_table} WHERE post_id = %d LIMIT 1", $homepage_id )
				);

				$data = array(
					'title'          => $seo_title,
					'description'    => $seo_desc,
					'og_title'       => $seo_title,
					'og_description' => $og_desc,
					'keyphrases'     => $keyphrases,
					'updated'        => current_time( 'mysql' ),
				);
				$formats = array( '%s', '%s', '%s', '%s', '%s', '%s' );

				if ( $existing ) {
					$wpdb->update( $aioseo_table, $data, array( 'post_id' => $homepage_id ), $formats, array( '%d' ) );
				} else {
					$data['post_id'] = $homepage_id;
					$data['created'] = current_time( 'mysql' );
					$wpdb->insert( $aioseo_table, $data, array_merge( $formats, array( '%d', '%s' ) ) );
				}

				$applied_via = 'AIOSEO';
			}
		}

		if ( ! $applied_via && defined( 'WPSEO_VERSION' ) ) {
			update_post_meta( $homepage_id, '_yoast_wpseo_title', $seo_title );
			update_post_meta( $homepage_id, '_yoast_wpseo_metadesc', $seo_desc );
			update_post_meta( $homepage_id, '_yoast_wpseo_focuskw', $focus_kw );
			update_post_meta( $homepage_id, '_yoast_wpseo_opengraph-description', $og_desc );
			$applied_via = 'Yoast';
		}

		if ( ! $applied_via ) {
			wp_send_json_error(
				array(
					'message' => __( 'No supported SEO plugin found (AIOSEO or Yoast). Please copy the values manually.', 'academy-starter' ),
				)
			);
		}

		clean_post_cache( $homepage_id );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %s: SEO plugin name */
					__( 'SEO applied via %s. Reload the homepage editor to see updated scores. You may also need to clear your site cache.', 'academy-starter' ),
					$applied_via
				),
			)
		);
	}

	/**
	 * Admin-post handler: generate or regenerate the REST API key.
	 *
	 * @return void
	 */
	public static function handle_generate_api_key() {
		check_admin_referer( 'academy_starter_generate_api_key' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'academy-starter' ) );
		}

		AcademyStarterRestApi::generate_api_key();

		wp_safe_redirect( admin_url( 'options-general.php?page=academy-starter&api_key_generated=1' ) );
		exit;
	}

	/**
	 * Sanitize style option.
	 *
	 * @param string $style Style key.
	 * @return string
	 */
	public static function sanitize_style( $style ) {
		$style   = sanitize_key( $style );
		$options = AcademyStarterStyles::get_style_options();

		return isset( $options[ $style ] ) ? $style : 'jazz_piano';
	}

	/**
	 * Sanitize and validate GA4 Measurement ID format.
	 *
	 * @param string $value Raw input.
	 * @return string
	 */
	public static function sanitize_ga4_id( $value ) {
		$value = strtoupper( sanitize_text_field( trim( $value ) ) );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^G-[A-Z0-9]{4,20}$/', $value ) ) {
			return $value;
		}

		add_settings_error(
			self::OPTION_GA4_ID,
			'invalid_ga4_id',
			__( 'GA4 Measurement ID must be in the format G-XXXXXXXXXX.', 'academy-starter' )
		);

		return get_option( self::OPTION_GA4_ID, '' );
	}

	/**
	 * Hide the front-page header when enabled.
	 *
	 * @return void
	 */
	public static function maybe_hide_header() {
		if ( ! is_front_page() || '1' !== get_option( self::OPTION_HIDE_HEADER, '' ) ) {
			return;
		}

		echo '<style>.site-header,#masthead{display:none!important}</style>' . "\n";
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_style = get_option( self::OPTION_STYLE, 'jazz_piano' );
		$style_data    = AcademyStarterStyles::get_style_data( $current_style );
		$homepage_id   = (int) get_option( 'page_on_front' );
		$homepage_link = $homepage_id ? get_edit_post_link( $homepage_id ) : null;
		$homepage_title = $homepage_id ? get_the_title( $homepage_id ) : null;
		$paid_url      = get_option( self::OPTION_PAID_URL, self::DEFAULT_PAID_URL );
		$free_form_id  = (int) get_option( self::OPTION_FREE_FORM_ID, 0 );
		$ga4_id        = get_option( self::OPTION_GA4_ID, '' );
		$about_text    = get_option( self::OPTION_ABOUT_TEXT, '' );

		if ( empty( $about_text ) ) {
			$about_text = self::build_default_about_text( $style_data );
		}

		$forms         = self::get_fluent_forms();
		$summary       = AcademyStarterAnalytics::get_summary();
		$daily_stats   = AcademyStarterAnalytics::get_daily_stats( 30 );
		?>
		<div class="wrap academy-starter-admin">
			<h1><?php esc_html_e( 'Academy Starter', 'academy-starter' ); ?></h1>

			<div class="academy-starter-admin-grid">
				<div class="academy-starter-card">
					<h2><?php esc_html_e( 'Landing Page Settings', 'academy-starter' ); ?></h2>
					<form method="post" action="options.php">
						<?php settings_fields( 'academy_starter_settings' ); ?>

						<table class="form-table" role="presentation">
							<tbody>
								<tr>
									<th scope="row">
										<label for="academy-starter-style"><?php esc_html_e( 'Piano Style/Focus', 'academy-starter' ); ?></label>
									</th>
									<td>
										<select id="academy-starter-style" name="<?php echo esc_attr( self::OPTION_STYLE ); ?>">
											<?php foreach ( AcademyStarterStyles::get_style_options() as $style_key => $style_label ) : ?>
												<option value="<?php echo esc_attr( $style_key ); ?>" <?php selected( $current_style, $style_key ); ?>>
													<?php echo esc_html( $style_label ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="academy-starter-paid-url"><?php esc_html_e( 'Paid Program Order Form URL', 'academy-starter' ); ?></label>
									</th>
									<td>
										<input id="academy-starter-paid-url" class="regular-text" type="url" name="<?php echo esc_attr( self::OPTION_PAID_URL ); ?>" value="<?php echo esc_attr( $paid_url ); ?>">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="academy-starter-free-form"><?php esc_html_e( 'Free Program Form', 'academy-starter' ); ?></label>
									</th>
									<td>
										<select id="academy-starter-free-form" name="<?php echo esc_attr( self::OPTION_FREE_FORM_ID ); ?>">
											<option value="0"><?php esc_html_e( 'Select a Fluent Form', 'academy-starter' ); ?></option>
											<?php foreach ( $forms as $form ) : ?>
												<option value="<?php echo esc_attr( $form['id'] ); ?>" <?php selected( $free_form_id, $form['id'] ); ?>>
													<?php echo esc_html( sprintf( '%1$s (ID: %2$d)', $form['title'], $form['id'] ) ); ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php if ( empty( $forms ) ) : ?>
											<p class="description"><?php esc_html_e( 'No published Fluent Forms were found.', 'academy-starter' ); ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="academy-starter-ga4-id"><?php esc_html_e( 'GA4 Measurement ID', 'academy-starter' ); ?></label>
									</th>
									<td>
										<input
											id="academy-starter-ga4-id"
											class="regular-text"
											type="text"
											name="<?php echo esc_attr( self::OPTION_GA4_ID ); ?>"
											value="<?php echo esc_attr( $ga4_id ); ?>"
											placeholder="G-XXXXXXXXXX"
										>
										<p class="description">
											<?php esc_html_e( 'Enter your GA4 Measurement ID to enable Google Analytics on this site. Leave blank to disable. Each microsite should have its own GA4 property.', 'academy-starter' ); ?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php esc_html_e( 'Hide Header on Homepage', 'academy-starter' ); ?>
									</th>
									<td>
										<label>
											<input
												type="checkbox"
												name="<?php echo esc_attr( self::OPTION_HIDE_HEADER ); ?>"
												value="1"
												<?php checked( '1', get_option( self::OPTION_HIDE_HEADER, '' ) ); ?>
											>
											<?php esc_html_e( 'Hide the site header and navigation on the front page (replaces WPCode snippet)', 'academy-starter' ); ?>
										</label>
									</td>
								</tr>
							</tbody>
						</table>

						<div class="academy-starter-about-editor">
							<label for="<?php echo esc_attr( self::OPTION_ABOUT_TEXT ); ?>" class="academy-starter-about-editor-label">
								<strong><?php esc_html_e( '"About" Section Text', 'academy-starter' ); ?></strong>
								<p class="description">
									<?php esc_html_e( 'One paragraph per line-break. Leave blank to use the style default. Each paragraph should be 1–2 sentences.', 'academy-starter' ); ?>
								</p>
							</label>
							<textarea
								id="<?php echo esc_attr( self::OPTION_ABOUT_TEXT ); ?>"
								name="<?php echo esc_attr( self::OPTION_ABOUT_TEXT ); ?>"
								rows="14"
								class="large-text"
								style="font-size:14px; line-height:1.6;"
							><?php echo esc_textarea( $about_text ); ?></textarea>
						</div>

						<?php submit_button( __( 'Save Settings', 'academy-starter' ) ); ?>
					</form><!-- END main settings form -->

					<!-- Reset form is intentionally outside the settings form -->
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:-10px; margin-bottom:20px;">
						<input type="hidden" name="action" value="academy_starter_reset_about_text">
						<?php wp_nonce_field( 'academy_starter_reset_about' ); ?>
						<button type="submit" class="button button-link" style="color:#b32d2e; font-size:13px;">
							<?php esc_html_e( '↺ Reset About text to style default', 'academy-starter' ); ?>
						</button>
						<?php if ( isset( $_GET['about_reset'] ) ) : ?>
							<span style="color:green; font-size:13px; margin-left:8px;">
								<?php esc_html_e( 'Reset. Edit the text above and click Save Settings.', 'academy-starter' ); ?>
							</span>
						<?php endif; ?>
					</form>

					<div class="academy-starter-shortcode-row">
						<code id="academy-starter-shortcode">[academy_starter_landing]</code>
						<button type="button" class="button" id="academy-starter-copy-shortcode">
							<?php esc_html_e( 'Copy Shortcode', 'academy-starter' ); ?>
						</button>
						<span class="academy-starter-shortcode-copied" id="academy-starter-copy-confirm" style="display:none;">
							<?php esc_html_e( 'Copied!', 'academy-starter' ); ?>
						</span>
					</div>

					<div class="academy-starter-api-key-row">
						<h3><?php esc_html_e( 'REST API Access', 'academy-starter' ); ?></h3>
						<p class="description">
							<?php esc_html_e( 'Use this key to authenticate requests to the stats endpoint from your aggregator plugin.', 'academy-starter' ); ?>
						</p>
						<p class="description">
							<strong><?php esc_html_e( 'Endpoint:', 'academy-starter' ); ?></strong>
							<code><?php echo esc_html( get_rest_url( null, 'academy-starter/v1/stats?api_key=YOUR_KEY' ) ); ?></code>
						</p>

						<?php $api_key = get_option( AcademyStarterRestApi::OPTION_API_KEY, '' ); ?>

						<?php if ( $api_key ) : ?>
							<div class="academy-starter-api-key-display">
								<code id="academy-starter-api-key"><?php echo esc_html( $api_key ); ?></code>
								<button type="button" class="button" id="academy-starter-copy-api-key">
									<?php esc_html_e( 'Copy Key', 'academy-starter' ); ?>
								</button>
								<span id="academy-starter-api-key-copied" class="academy-starter-shortcode-copied" style="display:none;">
									<?php esc_html_e( 'Copied!', 'academy-starter' ); ?>
								</span>
							</div>
						<?php endif; ?>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px;">
							<input type="hidden" name="action" value="academy_starter_generate_api_key">
							<?php wp_nonce_field( 'academy_starter_generate_api_key' ); ?>
							<button type="submit" class="button <?php echo $api_key ? 'button-secondary' : 'button-primary'; ?>">
								<?php echo $api_key ? esc_html__( 'Regenerate API Key', 'academy-starter' ) : esc_html__( 'Generate API Key', 'academy-starter' ); ?>
							</button>
							<?php if ( $api_key ) : ?>
								<span class="description" style="margin-left:8px;">
									<?php esc_html_e( 'Regenerating will invalidate the current key immediately.', 'academy-starter' ); ?>
								</span>
							<?php endif; ?>
						</form>
					</div>
				</div>

				<div class="academy-starter-side-column">
					<div class="academy-starter-card academy-starter-card-compact">
						<h2><?php esc_html_e( 'Analytics Summary', 'academy-starter' ); ?></h2>
						<div class="academy-starter-metrics">
							<div>
								<span><?php esc_html_e( 'Total Page Views', 'academy-starter' ); ?></span>
								<strong><?php echo esc_html( number_format_i18n( $summary['total_views'] ) ); ?></strong>
							</div>
							<div>
								<span><?php esc_html_e( 'Paid Button Clicks', 'academy-starter' ); ?></span>
								<strong><?php echo esc_html( number_format_i18n( $summary['paid_clicks'] ) ); ?></strong>
							</div>
							<div>
								<span><?php esc_html_e( 'Free Button Clicks', 'academy-starter' ); ?></span>
								<strong><?php echo esc_html( number_format_i18n( $summary['free_clicks'] ) ); ?></strong>
							</div>
							<div>
								<span><?php esc_html_e( 'Conversion Rate', 'academy-starter' ); ?></span>
								<strong><?php echo esc_html( number_format_i18n( $summary['conversion_rate'], 2 ) ); ?>%</strong>
							</div>
						</div>
						<div class="academy-starter-reset-row">
							<button
								type="button"
								id="academy-starter-reset-stats"
								class="button button-link-delete"
								data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'academy_starter_reset_stats' ) ); ?>"
								data-confirm-message="<?php echo esc_attr__( 'Are you sure you want to delete all stats? This cannot be undone.', 'academy-starter' ); ?>"
							>
								<?php esc_html_e( 'Reset All Stats', 'academy-starter' ); ?>
							</button>
							<span id="academy-starter-reset-confirm" class="academy-starter-reset-confirm" style="display:none;">
								<?php esc_html_e( 'Stats cleared.', 'academy-starter' ); ?>
							</span>
						</div>
					</div>

					<div class="academy-starter-card" style="margin-top: 20px;">
						<h2><?php esc_html_e( 'SEO Setup', 'academy-starter' ); ?></h2>
						<p class="description" style="margin-top:-8px; margin-bottom:16px;">
							<?php esc_html_e( 'Copy these into your SEO plugin (AIOSEO, Yoast, etc.) for the homepage of this site. Updates automatically when you change the Piano Style/Focus and save.', 'academy-starter' ); ?>
						</p>

						<?php if ( $homepage_link ) : ?>
							<div class="academy-starter-homepage-link">
								<span><?php esc_html_e( 'Homepage:', 'academy-starter' ); ?></span>
								<strong><?php echo esc_html( $homepage_title ); ?></strong>
								<a href="<?php echo esc_url( $homepage_link ); ?>" target="_blank" rel="noopener" class="button button-small">
									<?php esc_html_e( 'Edit Homepage →', 'academy-starter' ); ?>
								</a>
							</div>
							<div class="academy-starter-apply-seo-row academy-starter-apply-seo-row-top">
								<button type="button" id="academy-starter-apply-seo" class="button button-primary">
									<?php esc_html_e( '✦ Apply SEO to Homepage', 'academy-starter' ); ?>
								</button>
								<span id="academy-starter-apply-seo-result" class="academy-starter-apply-seo-result" style="display:none;"></span>
							</div>
						<?php elseif ( $homepage_id === 0 ) : ?>
							<div class="academy-starter-homepage-link academy-starter-homepage-warn">
								<?php esc_html_e( 'No static homepage is set. Go to Settings → Reading and set a static homepage page.', 'academy-starter' ); ?>
								<a href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>" target="_blank" rel="noopener">
									<?php esc_html_e( 'Fix this →', 'academy-starter' ); ?>
								</a>
							</div>
						<?php endif; ?>

						<?php
						$seo_fields = array(
							array(
								'id'    => 'academy-starter-seo-title',
								'label' => __( 'Page Title', 'academy-starter' ),
								'value' => $style_data['seo_title'] ?? '',
								'note'  => sprintf( __( '%d / 60 characters recommended', 'academy-starter' ), strlen( $style_data['seo_title'] ?? '' ) ),
							),
							array(
								'id'    => 'academy-starter-seo-desc',
								'label' => __( 'Meta Description', 'academy-starter' ),
								'value' => $style_data['seo_description'] ?? '',
								'note'  => sprintf( __( '%d / 160 characters max', 'academy-starter' ), strlen( $style_data['seo_description'] ?? '' ) ),
							),
							array(
								'id'    => 'academy-starter-focus-kw',
								'label' => __( 'Focus Keyword', 'academy-starter' ),
								'value' => $style_data['focus_keyword'] ?? '',
								'note'  => __( 'Enter this as the focus keyphrase in your SEO plugin', 'academy-starter' ),
							),
							array(
								'id'    => 'academy-starter-og-desc',
								'label' => __( 'OG / Social Description', 'academy-starter' ),
								'value' => $style_data['og_description'] ?? '',
								'note'  => __( 'Paste into Open Graph description field in your SEO plugin', 'academy-starter' ),
							),
						);
						?>

						<div class="academy-starter-seo-fields">
							<?php foreach ( $seo_fields as $field ) : ?>
								<div class="academy-starter-seo-field">
									<label><?php echo esc_html( $field['label'] ); ?></label>
									<div class="academy-starter-seo-field-row">
										<code id="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['value'] ); ?></code>
										<button
											type="button"
											class="button academy-starter-seo-copy"
											data-target="<?php echo esc_attr( $field['id'] ); ?>"
										><?php esc_html_e( 'Copy', 'academy-starter' ); ?></button>
									</div>
									<p class="description academy-starter-seo-note <?php echo ( strlen( $field['value'] ) > 155 ) ? 'academy-starter-seo-warn' : ''; ?>">
										<?php echo esc_html( $field['note'] ); ?>
									</p>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="academy-starter-seo-checklist">
							<h3><?php esc_html_e( 'Setup Checklist', 'academy-starter' ); ?></h3>
							<ul>
								<li><?php esc_html_e( '☐ Set Page Title in SEO plugin → Search Appearance → Homepage', 'academy-starter' ); ?></li>
								<li><?php esc_html_e( '☐ Set Meta Description in SEO plugin → Search Appearance → Homepage', 'academy-starter' ); ?></li>
								<li><?php esc_html_e( '☐ Set Focus Keyword / Keyphrase in SEO plugin', 'academy-starter' ); ?></li>
								<li><?php esc_html_e( '☐ Enable Open Graph in SEO plugin → Social Networks', 'academy-starter' ); ?></li>
								<li><?php esc_html_e( '☐ Set OG Description in SEO plugin → Social tab on the homepage', 'academy-starter' ); ?></li>
								<li><?php esc_html_e( '☐ Verify canonical URL is set to this site\'s homepage', 'academy-starter' ); ?></li>
								<li><?php esc_html_e( '☐ Enter GA4 Measurement ID above and save', 'academy-starter' ); ?></li>
							</ul>
						</div>

					</div>
				</div>
			</div>

			<div class="academy-starter-card academy-starter-daily-card">
				<h2><?php esc_html_e( 'Last 30 Days', 'academy-starter' ); ?></h2>
				<table class="widefat striped academy-starter-stats-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'academy-starter' ); ?></th>
							<th><?php esc_html_e( 'Views', 'academy-starter' ); ?></th>
							<th><?php esc_html_e( 'Paid Clicks', 'academy-starter' ); ?></th>
							<th><?php esc_html_e( 'Free Clicks', 'academy-starter' ); ?></th>
							<th><?php esc_html_e( 'Conversion Rate', 'academy-starter' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $daily_stats as $row ) :
							$total_clicks    = $row['paid_clicks'] + $row['free_clicks'];
							$daily_conv_rate = $row['views'] > 0 ? round( ( $total_clicks / $row['views'] ) * 100, 2 ) : 0;
						?>
							<tr>
								<td><?php echo esc_html( $row['date'] ); ?></td>
								<td>
									<?php if ( $row['views'] > 0 ) : ?>
										<button
											type="button"
											class="button-link academy-starter-day-views-btn"
											data-date="<?php echo esc_attr( $row['date'] ); ?>"
											data-count="<?php echo esc_attr( $row['views'] ); ?>"
										><?php echo esc_html( number_format_i18n( $row['views'] ) ); ?></button>
									<?php else : ?>
										0
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( number_format_i18n( $row['paid_clicks'] ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( $row['free_clicks'] ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( $daily_conv_rate, 2 ) ); ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div id="academy-starter-day-modal-overlay" class="academy-starter-day-modal-overlay" style="display:none;" aria-hidden="true">
				<div class="academy-starter-day-modal" role="dialog" aria-modal="true" aria-labelledby="academy-starter-day-modal-title">
					<div class="academy-starter-day-modal-header">
						<h2 id="academy-starter-day-modal-title">
							<?php esc_html_e( 'Views for', 'academy-starter' ); ?> <span id="academy-starter-day-modal-date"></span>
							<span id="academy-starter-day-modal-unique" class="academy-starter-day-modal-unique" style="display:none;"></span>
						</h2>
						<button type="button" id="academy-starter-day-modal-close" class="academy-starter-day-modal-close" aria-label="<?php esc_attr_e( 'Close', 'academy-starter' ); ?>">&times;</button>
					</div>
					<div class="academy-starter-day-modal-body">
						<div id="academy-starter-day-modal-loading" style="display:none;">
							<?php esc_html_e( 'Loading…', 'academy-starter' ); ?>
						</div>
						<table class="widefat striped" id="academy-starter-day-modal-table" style="display:none;">
							<thead>
								<tr>
									<th><?php esc_html_e( '#', 'academy-starter' ); ?></th>
									<th><?php esc_html_e( 'Time', 'academy-starter' ); ?></th>
									<th><?php esc_html_e( 'IP Address', 'academy-starter' ); ?></th>
								</tr>
							</thead>
							<tbody id="academy-starter-day-modal-tbody"></tbody>
						</table>
						<p id="academy-starter-day-modal-empty" style="display:none;">
							<?php esc_html_e( 'No view records found for this date.', 'academy-starter' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Build default About editor content from style data.
	 *
	 * @param array<string, mixed> $style_data Style configuration.
	 * @return string
	 */
	private static function build_default_about_text( array $style_data ) {
		$raw = isset( $style_data['about_paragraph'] ) ? $style_data['about_paragraph'] : '';

		if ( is_array( $raw ) ) {
			$paragraphs = $raw;
		} else {
			$parts      = preg_split( '/(?<=\.)\s+(?=[A-Z])/', trim( $raw ), -1, PREG_SPLIT_NO_EMPTY );
			$parts      = $parts ? $parts : array( $raw );
			$chunks     = array_chunk( $parts, 2 );
			$paragraphs = array_map( static function ( $c ) { return implode( ' ', $c ); }, $chunks );
		}

		if ( ! empty( $style_data['style_intro'] ) ) {
			$parts  = preg_split( '/(?<=\.)\s+(?=[A-Z])/', trim( $style_data['style_intro'] ), -1, PREG_SPLIT_NO_EMPTY );
			$parts  = $parts ? $parts : array( $style_data['style_intro'] );
			$chunks = array_chunk( $parts, 2 );
			foreach ( $chunks as $chunk ) {
				$paragraphs[] = implode( ' ', $chunk );
			}
		}

		return implode( "\n\n", array_map( 'trim', $paragraphs ) );
	}

	/**
	 * Fetch published Fluent Forms.
	 *
	 * @return array<int, array{id:int,title:string}>
	 */
	private static function get_fluent_forms() {
		$forms = array();

		if ( function_exists( 'wpFluent' ) ) {
			try {
				$records = wpFluent()->table( 'fluentform_forms' )->select( array( 'id', 'title' ) )->where( 'status', 'published' )->orderBy( 'id', 'desc' )->get();

				foreach ( $records as $record ) {
					$forms[] = array(
						'id'    => absint( $record->id ),
						'title' => sanitize_text_field( $record->title ),
					);
				}
			} catch ( Throwable $exception ) {
				return array();
			}
		}

		if ( empty( $forms ) ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'fluentform_forms';
			$table_like = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

			if ( $table_like !== $table_name ) {
				return array();
			}

			$records = $wpdb->get_results( "SELECT id, title FROM {$table_name} WHERE status = 'published' ORDER BY id DESC" );

			foreach ( $records as $record ) {
				$forms[] = array(
					'id'    => absint( $record->id ),
					'title' => sanitize_text_field( $record->title ),
				);
			}
		}

		return $forms;
	}
}
