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
	 * Send contact data to SJE CRM via JE_CRM_Sender.
	 *
	 * @param object $funnel Funnel object.
	 * @param object $lead   Lead object.
	 * @return bool True if send succeeded, false otherwise.
	 */
	public function send( $funnel, $lead ) {
		$sje_tag_id = (int) get_option( 'jem_sje_tag_id', 121 );

		$payload = array(
			'email'               => $lead->email,
			'first_name'          => $lead->first_name,
			'last_name'           => $lead->last_name,
			'add_tags'            => $sje_tag_id > 0 ? array( $sje_tag_id ) : array(),
			'custom_fields'       => array(
				'coupon_code'         => $lead->coupon_code,
				'jem_product_name'    => $funnel->name,
				'jem_product_url'     => $funnel->product_url,
				'jem_expiration_date' => $lead->coupon_expires,
				'jem_music_link'     => home_url( '/?jem_download=' . $lead->download_token ),
			),
		);

		$result = JE_CRM_Sender::send( $payload );

		$success = ! empty( $result['success'] );
		$message = isset( $result['message'] ) ? $result['message'] : '';

		$this->database->update_lead( $lead->id, array(
			'webhook_sent'     => $success ? 1 : 0,
			'webhook_response' => 'SJE CRM: ' . $message,
		) );

		return $success;
	}
}
