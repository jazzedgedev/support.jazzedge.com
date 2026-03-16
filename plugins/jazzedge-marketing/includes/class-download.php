<?php
/**
 * Jazzedge Marketing - Download Handler
 *
 * @package Jazzedge_Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JEM_Download
 */
class JEM_Download {

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
	 * Register template_redirect hook. Called from plugin.
	 */
	public function init() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	/**
	 * Handle download request via template_redirect.
	 */
	public function handle() {
		$token = isset( $_GET['jem_download'] ) ? sanitize_text_field( wp_unslash( $_GET['jem_download'] ) ) : '';
		if ( empty( $token ) ) {
			return;
		}

		$lead = $this->database->get_lead_by_token( $token );
		if ( ! $lead ) {
			wp_die( esc_html__( 'Invalid or expired link.', 'jazzedge-marketing' ), '', array( 'response' => 404 ) );
		}

		$funnel = $this->database->get_funnel( $lead->funnel_id );
		if ( ! $funnel || empty( $funnel->media_id ) ) {
			wp_die( esc_html__( 'File not found.', 'jazzedge-marketing' ), '', array( 'response' => 404 ) );
		}

		$file = get_attached_file( (int) $funnel->media_id );
		if ( ! $file || ! file_exists( $file ) ) {
			wp_die( esc_html__( 'File not found.', 'jazzedge-marketing' ), '', array( 'response' => 404 ) );
		}

		// Log download_click event.
		$this->database->log_event( $lead->id, $lead->funnel_id, 'download_click' );

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( basename( $file ) ) . '"' );
		header( 'Content-Length: ' . filesize( $file ) );
		readfile( $file );
		exit;
	}
}
