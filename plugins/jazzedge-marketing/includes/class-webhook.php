<?php
/**
 * Jazzedge Marketing - Webhook
 *
 * @package Jazzedge_Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JEM_Webhook
 */
class JEM_Webhook {

	/**
	 * @var JEM_Database
	 */
	private $database;

	/**
	 * Constructor.
	 *
	 * @param JEM_Database $database Database instance.
	 */
	public function __construct( $database ) {
		$this->database = $database;
	}

	/**
	 * Send webhook POST to funnel URL.
	 *
	 * @param object $funnel Funnel object.
	 * @param object $lead   Lead object.
	 * @return bool True if 2xx response, false otherwise.
	 */
	public function send( $funnel, $lead ) {
		$body = array(
			'first_name'          => $lead->first_name,
			'last_name'           => $lead->last_name,
			'full_name'           => $lead->first_name . ' ' . $lead->last_name,
			'email'               => $lead->email,
			'coupon_code'         => $lead->coupon_code,
			'jem_product_name'    => $funnel->name,
			'jem_product_url'     => $funnel->product_url,
			'jem_expiration_date' => $lead->coupon_expires,
		);

		$response = wp_remote_post(
			$funnel->webhook_url,
			array(
				'body'    => $body,
				'timeout' => 15,
			)
		);

		$status_code = wp_remote_retrieve_response_code( $response );
		$body_raw    = wp_remote_retrieve_body( $response );

		$webhook_response = 'HTTP ' . $status_code . ': ' . $body_raw;

		// Store response in lead regardless of outcome.
		$this->database->update_lead( $lead->id, array(
			'webhook_response' => $webhook_response,
			'webhook_sent'      => is_wp_error( $response ) ? 0 : 1,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return $status_code >= 200 && $status_code < 300;
	}
}
