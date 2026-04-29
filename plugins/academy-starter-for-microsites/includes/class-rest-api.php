<?php
/**
 * REST API endpoint for Academy Starter analytics.
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes analytics stats for external aggregators.
 */
class AcademyStarterRestApi {
	const NAMESPACE = 'academy-starter/v1';
	const OPTION_API_KEY = 'academy_starter_api_key';

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_stats' ),
				'permission_callback' => array( __CLASS__, 'check_api_key' ),
			)
		);
	}

	public static function check_api_key( WP_REST_Request $request ) {
		$stored_key = get_option( self::OPTION_API_KEY, '' );

		if ( empty( $stored_key ) ) {
			return false;
		}

		$auth_header = $request->get_header( 'authorization' );
		if ( $auth_header && 0 === strpos( $auth_header, 'Bearer ' ) ) {
			$provided_key = trim( substr( $auth_header, 7 ) );

			return hash_equals( $stored_key, $provided_key );
		}

		$provided_key = (string) $request->get_param( 'api_key' );
		if ( empty( $provided_key ) ) {
			return false;
		}

		return hash_equals( $stored_key, $provided_key );
	}

	/**
	 * Return stats summary and daily data.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public static function get_stats( WP_REST_Request $request ) {
		$days    = absint( $request->get_param( 'days' ) );
		$days    = ( $days > 0 && $days <= 365 ) ? $days : 30;
		$summary = AcademyStarterAnalytics::get_summary();
		$daily   = AcademyStarterAnalytics::get_daily_stats( $days );

		return rest_ensure_response(
			array(
				'site'    => array(
					'url'   => get_site_url(),
					'name'  => get_bloginfo( 'name' ),
					'style' => get_option( AcademyStarterAdmin::OPTION_STYLE, 'jazz_piano' ),
				),
				'summary' => $summary,
				'daily'   => $daily,
			)
		);
	}

	/**
	 * Generate a new random API key and save it.
	 *
	 * @return string
	 */
	public static function generate_api_key() {
		$key = wp_generate_password( 32, false );
		update_option( self::OPTION_API_KEY, $key );

		return $key;
	}
}
