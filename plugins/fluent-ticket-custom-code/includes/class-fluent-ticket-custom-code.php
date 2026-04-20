<?php
/**
 * Fluent Ticket Custom Code — sidebar widget + Keap contact resolution.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Fluent_Ticket_Custom_Code {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'fluent_support/customer_extra_widgets', array( $this, 'register_widgets' ), 50, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_ticket_assets' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'fluent-ticket-custom-code',
			false,
			dirname( plugin_basename( FLUENT_TICKET_CUSTOM_CODE_FILE ) ) . '/languages'
		);
	}

	/**
	 * Optional overrides in wp-config.php:
	 * define( 'FTCC_KEAP_API_TOKEN', '...' );
	 * define( 'FTCC_MEMBERIUM_AUTH_KEY', '...' );
	 * define( 'FTCC_MEMBERIUM_AUTOLOGIN_BASE', 'https://jazzedge.academy/' );
	 */
	private function keap_api_token() {
		if ( defined( 'FTCC_KEAP_API_TOKEN' ) && FTCC_KEAP_API_TOKEN ) {
			return FTCC_KEAP_API_TOKEN;
		}
		return '';
	}

	private function memberium_auth_key() {
		if ( defined( 'FTCC_MEMBERIUM_AUTH_KEY' ) && FTCC_MEMBERIUM_AUTH_KEY ) {
			return FTCC_MEMBERIUM_AUTH_KEY;
		}
		return '';
	}

	private function memberium_base_url() {
		if ( defined( 'FTCC_MEMBERIUM_AUTOLOGIN_BASE' ) && FTCC_MEMBERIUM_AUTOLOGIN_BASE ) {
			return trailingslashit( FTCC_MEMBERIUM_AUTOLOGIN_BASE );
		}
		return 'https://jazzedge.academy/';
	}

	public function enqueue_ticket_assets( $hook ) {
		$on_fs = isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'fluent-support' ) !== false;
		$hook  = (string) $hook;
		if ( ! $on_fs && strpos( $hook, 'fluent-support' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'fluent-ticket-custom-code-ticket',
			FLUENT_TICKET_CUSTOM_CODE_URL . 'assets/css/ticket.css',
			array(),
			FLUENT_TICKET_CUSTOM_CODE_VERSION
		);

		wp_enqueue_script(
			'fluent-ticket-custom-code-ticket',
			FLUENT_TICKET_CUSTOM_CODE_URL . 'assets/js/ticket.js',
			array( 'jquery' ),
			FLUENT_TICKET_CUSTOM_CODE_VERSION,
			true
		);
	}

	/**
	 * @param array $widgets Existing widgets.
	 * @param object $customer Fluent Support customer object.
	 * @return array
	 */
	public function register_widgets( $widgets, $customer ) {
		$ticket_data = $this->get_ticket_data();
		$widgets['fluent_ticket_customer_tools'] = array(
			'header'    => __( 'Customer Tools', 'fluent-ticket-custom-code' ),
			'body_html' => $this->build_customer_tools_html( $ticket_data, $customer ),
		);
		return $widgets;
	}

	/**
	 * Resolve current ticket from admin URL and load customer email.
	 *
	 * @return array|null
	 */
	private function get_ticket_data() {
		$current_url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$ticket_id   = 0;
		if ( preg_match( '/tickets\/(\d+)/', $current_url, $matches ) ) {
			$ticket_id = (int) $matches[1];
		}
		if ( ! $ticket_id ) {
			return null;
		}

		global $wpdb;
		$ticket_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d",
				$ticket_id
			),
			ARRAY_A
		);

		if ( ! $ticket_data ) {
			return null;
		}

		$customer_email = '';
		if ( ! empty( $ticket_data['customer_id'] ) ) {
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT email FROM {$wpdb->prefix}fs_persons WHERE id = %d",
					$ticket_data['customer_id']
				),
				ARRAY_A
			);
			if ( $row && ! empty( $row['email'] ) ) {
				$customer_email = $row['email'];
			}
		}

		$ticket_data['customer_email'] = $customer_email;
		return $ticket_data;
	}

	/**
	 * @param array|null $ticket_data Row from fs_tickets + customer_email.
	 * @param object|null $customer   Customer model.
	 */
	private function build_customer_tools_html( $ticket_data, $customer ) {
		$html = '<div class="ftcc-tools" id="fluent-ticket-custom-code-widget">';

		if ( ! $ticket_data || empty( $ticket_data['customer_email'] ) ) {
			$html .= '<p class="ftcc-muted">' . esc_html__( 'No customer email on this ticket.', 'fluent-ticket-custom-code' ) . '</p>';
			$html .= '</div>';
			return $html;
		}

		$primary_email = $ticket_data['customer_email'];
		$alternate_email = '';
		if ( $customer && isset( $customer->title ) && is_string( $customer->title ) && strlen( $customer->title ) > 6 && false !== strpos( $customer->title, '@' ) ) {
			if ( filter_var( $customer->title, FILTER_VALIDATE_EMAIL ) ) {
				$alternate_email = $customer->title;
			}
		}

		$resolved          = $this->resolve_keap_contact( $primary_email, $alternate_email );
		$contact_id        = $resolved['contact_id'];
		$keap_search_email = $resolved['keap_search_email'];

		if ( $contact_id ) {
			$keap_search_url = add_query_arg(
				array(
					'view'       => 'edit',
					'ID'         => $contact_id,
					'searchTerm' => $keap_search_email,
				),
				'https://app.infusionsoft.com/core/Contact/manageContact.jsp'
			);
		} else {
			$keap_search_url = 'https://app.infusionsoft.com/core/app/searchResults/searchResults?searchTerm=' . rawurlencode( $keap_search_email );
		}

		if ( $contact_id ) {
			/* translators: %s: Keap contact ID */
			$find_keap_label = sprintf( __( 'View in Keap (%s)', 'fluent-ticket-custom-code' ), $contact_id );
		} else {
			$find_keap_label = __( 'Find in Keap', 'fluent-ticket-custom-code' );
		}
		$html .= '<div class="ftcc-card ftcc-card--keap">';
		$html .= '<a class="ftcc-row-link" href="' . esc_url( $keap_search_url ) . '" target="_blank" rel="noopener noreferrer">';
		$html .= $this->icon_magnifying_glass();
		$html .= '<span>' . esc_html( $find_keap_label ) . '</span>';
		$html .= '</a></div>';

		$student_url = admin_url( 'admin.php?page=je-keap-manager&tab=find&email=' . rawurlencode( $primary_email ) );
		$html       .= '<div class="ftcc-card ftcc-card--student">';
		$html       .= '<a class="ftcc-row-link" href="' . esc_url( $student_url ) . '" target="_blank" rel="noopener noreferrer">';
		$html       .= $this->icon_user_circle();
		$html       .= '<span>' . esc_html__( 'Lookup Student', 'fluent-ticket-custom-code' ) . '</span>';
		$html       .= '</a></div>';

		$auth   = $this->memberium_auth_key();
		$token  = $this->keap_api_token();

		if ( ! $token ) {
			$html .= '<p class="ftcc-note ftcc-note--warn">' . esc_html__( 'Autologin links need a Keap API token. Define FTCC_KEAP_API_TOKEN in wp-config.php.', 'fluent-ticket-custom-code' ) . '</p>';
			$html .= '</div>';
			return $html;
		}

		if ( ! $contact_id ) {
			$html .= '<p class="ftcc-note">' . esc_html__( 'No Keap contact found for this email.', 'fluent-ticket-custom-code' ) . '</p>';
			$html .= '</div>';
			return $html;
		}

		if ( ! $auth ) {
			$html .= '<p class="ftcc-note ftcc-note--warn">' . esc_html__( 'Autologin links need a Memberium auth key. Define FTCC_MEMBERIUM_AUTH_KEY in wp-config.php.', 'fluent-ticket-custom-code' ) . '</p>';
			$html .= '</div>';
			return $html;
		}

		$base = $this->memberium_base_url();
		$q    = static function ( $id, $email, $redir ) use ( $base, $auth ) {
			return $base . '?memb_autologin=yes&auth_key=' . rawurlencode( $auth ) . '&Id=' . rawurlencode( (string) $id ) . '&Email=' . rawurlencode( (string) $email ) . '&redir=' . rawurlencode( $redir );
		};

		$dashboard_url = $q( $contact_id, $keap_search_email, '/dashboard/' );
		$html         .= '<div class="ftcc-card ftcc-card--dashboard">';
		$html         .= '<div class="ftcc-card-head">';
		$html         .= '<div class="ftcc-card-title">' . $this->icon_link() . ' <span>' . esc_html__( 'Dashboard Autologin Link', 'fluent-ticket-custom-code' ) . '</span></div>';
		$html         .= '<button type="button" class="ftcc-copy copy-autologin-btn" data-url="' . esc_attr( $dashboard_url ) . '">' . esc_html__( 'Copy', 'fluent-ticket-custom-code' ) . '</button>';
		$html         .= '</div>';
		$html         .= '</div>';

		$fluent_cart_url = $q( $contact_id, $keap_search_email, '/account' );
		$html           .= '<div class="ftcc-card ftcc-card--fluent-cart">';
		$html           .= '<div class="ftcc-card-head">';
		$html           .= '<div class="ftcc-card-title">' . $this->icon_shopping_cart() . ' <span>' . esc_html__( 'Fluent Cart Order — Account Autologin', 'fluent-ticket-custom-code' ) . '</span></div>';
		$html           .= '<button type="button" class="ftcc-copy ftcc-copy--cart copy-autologin-btn" data-url="' . esc_attr( $fluent_cart_url ) . '">' . esc_html__( 'Copy', 'fluent-ticket-custom-code' ) . '</button>';
		$html           .= '</div>';
		$html           .= '</div>';

		$card_url = $q( $contact_id, $primary_email, '/card/' );
		$html    .= '<div class="ftcc-card ftcc-card--card">';
		$html    .= '<div class="ftcc-card-head">';
		$html    .= '<div class="ftcc-card-title">' . $this->icon_credit_card() . ' <span>' . esc_html__( 'Card Update Autologin Link', 'fluent-ticket-custom-code' ) . '</span></div>';
		$html    .= '<button type="button" class="ftcc-copy ftcc-copy--gold copy-autologin-btn" data-url="' . esc_attr( $card_url ) . '">' . esc_html__( 'Copy', 'fluent-ticket-custom-code' ) . '</button>';
		$html    .= '</div>';
		$html    .= '</div>';

		if ( $alternate_email ) {
			$dash_alt = $q( $contact_id, $alternate_email, '/dashboard/' );
			$html    .= '<div class="ftcc-card ftcc-card--dashboard-alt">';
			$html    .= '<div class="ftcc-card-head">';
			$html    .= '<div class="ftcc-card-title">' . $this->icon_link() . ' <span>' . esc_html__( 'Dashboard Autologin Link (Alt Email)', 'fluent-ticket-custom-code' ) . '</span></div>';
			$html    .= '<button type="button" class="ftcc-copy copy-autologin-btn" data-url="' . esc_attr( $dash_alt ) . '">' . esc_html__( 'Copy', 'fluent-ticket-custom-code' ) . '</button>';
			$html    .= '</div>';
			$html    .= '</div>';

			$fluent_cart_alt = $q( $contact_id, $alternate_email, '/account' );
			$html           .= '<div class="ftcc-card ftcc-card--fluent-cart-alt">';
			$html           .= '<div class="ftcc-card-head">';
			$html           .= '<div class="ftcc-card-title">' . $this->icon_shopping_cart() . ' <span>' . esc_html__( 'Fluent Cart Order — Account Autologin (Alt Email)', 'fluent-ticket-custom-code' ) . '</span></div>';
			$html           .= '<button type="button" class="ftcc-copy ftcc-copy--cart-alt copy-autologin-btn" data-url="' . esc_attr( $fluent_cart_alt ) . '">' . esc_html__( 'Copy', 'fluent-ticket-custom-code' ) . '</button>';
			$html           .= '</div>';
			$html           .= '</div>';

			$card_alt = $q( $contact_id, $alternate_email, '/card/' );
			$html    .= '<div class="ftcc-card ftcc-card--card-alt">';
			$html    .= '<div class="ftcc-card-head">';
			$html    .= '<div class="ftcc-card-title">' . $this->icon_credit_card() . ' <span>' . esc_html__( 'Card Update Autologin Link (Alt Email)', 'fluent-ticket-custom-code' ) . '</span></div>';
			$html    .= '<button type="button" class="ftcc-copy ftcc-copy--amber copy-autologin-btn" data-url="' . esc_attr( $card_alt ) . '">' . esc_html__( 'Copy', 'fluent-ticket-custom-code' ) . '</button>';
			$html    .= '</div>';
			$html    .= '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * @return array{contact_id:string,keap_search_email:string,found_email_source:string}
	 */
	private function resolve_keap_contact( $primary_email, $alternate_email ) {
		$out = array(
			'contact_id'        => '',
			'keap_search_email' => $primary_email,
			'found_email_source' => 'primary',
		);

		$token = $this->keap_api_token();
		if ( ! $token ) {
			return $out;
		}

		$data = $this->keap_fetch_contacts_by_email( $primary_email, $token );
		if ( empty( $data['contacts'][0]['id'] ) && $alternate_email ) {
			$data = $this->keap_fetch_contacts_by_email( $alternate_email, $token );
			if ( ! empty( $data['contacts'][0]['id'] ) ) {
				$out['keap_search_email']         = $alternate_email;
				$out['found_email_source']      = 'alternate';
			}
		}

		if ( ! empty( $data['contacts'][0]['id'] ) ) {
			$out['contact_id'] = (string) $data['contacts'][0]['id'];
		}

		return $out;
	}

	private function keap_fetch_contacts_by_email( $email, $token ) {
		$url = 'https://api.infusionsoft.com/crm/rest/v1/contacts?email=' . rawurlencode( $email );
		$res = wp_remote_get(
			$url,
			array(
				'timeout' => 12,
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $res ) || (int) wp_remote_retrieve_response_code( $res ) !== 200 ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		return is_array( $body ) ? $body : array();
	}

	/** Heroicons 2 outline, 20px box */
	private function icon_wrap( $path_d ) {
		return '<svg class="ftcc-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' . esc_attr( $path_d ) . '" /></svg>';
	}

	private function icon_magnifying_glass() {
		return $this->icon_wrap( 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z' );
	}

	private function icon_link() {
		return $this->icon_wrap( 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.318 6.245' );
	}

	private function icon_credit_card() {
		return $this->icon_wrap( 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z' );
	}

	private function icon_user_circle() {
		return $this->icon_wrap( 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z' );
	}

	private function icon_shopping_cart() {
		return $this->icon_wrap( 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0z' );
	}
}
