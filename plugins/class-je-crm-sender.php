<?php
/**
 * JE CRM Sender
 *
 * Standalone helper class for sending contact data to the SJE CRM webhook endpoint.
 * Drop this file into any WordPress plugin on any site that needs to push contacts
 * to the central CRM (e.g. support.jazzedge.com).
 *
 * REQUIRED: Define these constants in wp-config.php on the sending site:
 *
 *   JE_CRM_ENDPOINT  — Full webhook URL, e.g. https://support.jazzedge.com/wp-json/jazzedge/v1/crm
 *   JE_CRM_API_KEY   — The shared secret key (configured in CRM Webhook settings)
 *
 * Usage:
 *
 *   $result = JE_CRM_Sender::send([
 *       'email'       => 'user@example.com',
 *       'first_name'  => 'Jane',
 *       'last_name'   => 'Doe',
 *       'status'      => 'subscribed',
 *       'add_tags'    => [1, 2],
 *       'add_lists'   => [1],
 *       'custom_fields' => ['field_slug' => 'value'],
 *   ]);
 *
 *   if ($result['success']) {
 *       // Contact updated
 *   } else {
 *       // $result['message'] contains error
 *   }
 *
 * @package JE_CRM_Sender
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'JE_CRM_Sender' ) ) {

	/**
	 * Static helper for sending contact data to the SJE CRM webhook.
	 */
	class JE_CRM_Sender {

		/**
		 * Send contact data to the CRM webhook endpoint.
		 *
		 * @param array $data Contact data. Keys: email (required), first_name, last_name,
		 *                   status, add_tags, remove_tags, add_lists, remove_lists, custom_fields.
		 * @return array Response from the endpoint. Always includes 'success' (bool) and 'message' (string).
		 */
		public static function send( $data ) {
			if ( ! defined( 'JE_CRM_ENDPOINT' ) || ! defined( 'JE_CRM_API_KEY' ) ) {
				return array(
					'success' => false,
					'message' => 'JE CRM not configured.',
				);
			}

			$payload = array_merge( $data, array( 'api_key' => JE_CRM_API_KEY ) );

			$response = wp_remote_post(
				JE_CRM_ENDPOINT,
				array(
					'body'      => wp_json_encode( $payload ),
					'headers'   => array(
						'Content-Type' => 'application/json',
					),
					'timeout'   => 15,
					'sslverify' => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				return array(
					'success' => false,
					'message'  => $response->get_error_message(),
				);
			}

			$body = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
				return array(
					'success' => false,
					'message'  => 'Invalid response from CRM endpoint.',
				);
			}

			return $decoded;
		}
	}
}
