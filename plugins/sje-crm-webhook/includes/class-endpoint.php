<?php
/**
 * REST API endpoint for SJE CRM Webhook
 *
 * Handles POST requests to create/update FluentCRM contacts.
 *
 * @package SJE_CRM_Webhook
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SJE_CRM_Webhook_Endpoint {

	/**
	 * Register REST routes
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes() {
		register_rest_route(
			'jazzedge/v1',
			'/crm',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_request' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Handle incoming webhook request
	 *
	 * @param WP_REST_Request $request The request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_request( $request ) {
		try {
			// 1. Try JSON body first
			$body    = $request->get_body();
			$payload = array();

			if ( ! empty( $body ) ) {
				$payload = json_decode( $body, true );
				if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $payload ) ) {
					return new WP_REST_Response(
						array( 'success' => false, 'message' => 'Invalid JSON.' ),
						400
					);
				}
			}

			// 2. Fall back to query params if body is empty (e.g. Keap HTTP Actions)
			if ( empty( $payload ) ) {
				$params = $request->get_query_params();
				if ( ! empty( $params ) ) {
					$payload = $params;

					// Normalize add_tags / remove_tags from comma-separated string to array
					foreach ( array( 'add_tags', 'remove_tags', 'add_lists', 'remove_lists' ) as $key ) {
						if ( isset( $payload[ $key ] ) && is_string( $payload[ $key ] ) ) {
							$payload[ $key ] = array_filter(
								array_map( 'intval', explode( ',', $payload[ $key ] ) )
							);
						}
					}
				}
			}

			if ( empty( $payload ) ) {
				return new WP_REST_Response(
					array( 'success' => false, 'message' => 'Empty request — send JSON body or query parameters.' ),
					400
				);
			}

			// 3. Validate api_key
			$stored_key = get_option( 'sje_crm_api_key', '' );
			$sent_key   = isset( $payload['api_key'] ) ? $payload['api_key'] : '';

			if ( empty( $sent_key ) || empty( $stored_key ) || ! hash_equals( (string) $stored_key, (string) $sent_key ) ) {
				SJE_CRM_Webhook_Logger::log( array(
					'email'     => isset( $payload['email'] ) ? $payload['email'] : '',
					'payload'   => $payload,
					'status'    => 'error',
					'message'   => 'Invalid or missing API key',
				) );
				return new WP_REST_Response(
					array( 'success' => false, 'message' => 'Unauthorized.' ),
					401
				);
			}

			// 4. Validate email
			$email = isset( $payload['email'] ) ? trim( $payload['email'] ) : '';
			if ( empty( $email ) || ! is_email( $email ) ) {
				return new WP_REST_Response(
					array( 'success' => false, 'message' => 'Valid email is required.' ),
					400
				);
			}

			// 5. Extract fields
			$first_name    = isset( $payload['first_name'] ) ? sanitize_text_field( $payload['first_name'] ) : null;
			$last_name     = isset( $payload['last_name'] ) ? sanitize_text_field( $payload['last_name'] ) : null;
			$status        = isset( $payload['status'] ) ? sanitize_text_field( $payload['status'] ) : null;
			$add_tags      = isset( $payload['add_tags'] ) && is_array( $payload['add_tags'] ) ? array_map( 'intval', $payload['add_tags'] ) : array();
			$remove_tags   = isset( $payload['remove_tags'] ) && is_array( $payload['remove_tags'] ) ? array_map( 'intval', $payload['remove_tags'] ) : array();
			$add_lists     = isset( $payload['add_lists'] ) && is_array( $payload['add_lists'] ) ? array_map( 'intval', $payload['add_lists'] ) : array();
			$remove_lists  = isset( $payload['remove_lists'] ) && is_array( $payload['remove_lists'] ) ? array_map( 'intval', $payload['remove_lists'] ) : array();
			$custom_fields = isset( $payload['custom_fields'] ) && is_array( $payload['custom_fields'] ) ? $payload['custom_fields'] : null;

			// 6. Build contact data
			$contact_data = array( 'email' => $email );

			if ( $first_name !== null && $first_name !== '' ) {
				$contact_data['first_name'] = $first_name;
			}
			if ( $last_name !== null && $last_name !== '' ) {
				$contact_data['last_name'] = $last_name;
			}
			if ( $status !== null && $status !== '' ) {
				$contact_data['status'] = $status;
			}
			if ( $custom_fields !== null ) {
				$contact_data['custom_values'] = $custom_fields;
			}

			// 7. Call FluentCRM
			if ( ! function_exists( 'FluentCrmApi' ) ) {
				SJE_CRM_Webhook_Logger::log( array(
					'email'     => $email,
					'payload'   => $payload,
					'status'    => 'error',
					'message'   => 'FluentCRM is not available',
				) );
				return new WP_REST_Response(
					array( 'success' => false, 'message' => 'FluentCRM is not available.' ),
					500
				);
			}

			$api     = FluentCrmApi( 'contacts' );
			$contact = $api->createOrUpdate( $contact_data );

			if ( ! $contact ) {
				SJE_CRM_Webhook_Logger::log( array(
					'email'     => $email,
					'payload'   => $payload,
					'status'    => 'error',
					'message'   => 'FluentCRM createOrUpdate returned no contact',
				) );
				return new WP_REST_Response(
					array( 'success' => false, 'message' => 'Failed to create or update contact.' ),
					500
				);
			}

			if ( ! empty( $add_tags ) ) {
				$contact->attachTags( $add_tags );
			}
			if ( ! empty( $remove_tags ) ) {
				$contact->detachTags( $remove_tags );
			}
			if ( ! empty( $add_lists ) ) {
				$contact->attachLists( $add_lists );
			}
			if ( ! empty( $remove_lists ) ) {
				$contact->detachLists( $remove_lists );
			}

			// 8. Log success
			SJE_CRM_Webhook_Logger::log( array(
				'email'     => $email,
				'payload'   => $payload,
				'status'    => 'success',
				'message'   => 'Contact updated',
			) );

			// 9. Return success
			return new WP_REST_Response(
				array( 'success' => true, 'message' => 'Contact updated.' ),
				200
			);

		} catch ( Exception $e ) {
			SJE_CRM_Webhook_Logger::log( array(
				'email'     => isset( $payload['email'] ) ? $payload['email'] : '',
				'payload'   => isset( $payload ) ? $payload : array(),
				'status'    => 'error',
				'message'   => $e->getMessage(),
			) );
			return new WP_REST_Response(
				array( 'success' => false, 'message' => 'An error occurred.' ),
				500
			);
		}
	}
}
