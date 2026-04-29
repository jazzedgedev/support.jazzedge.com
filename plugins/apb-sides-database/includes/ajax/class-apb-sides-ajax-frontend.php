<?php
/**
 * Frontend AJAX (public search).
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers nopriv and auth search handlers.
 */
class APB_Sides_Ajax_Frontend {

	public function __construct() {
		add_action( 'wp_ajax_nopriv_apb_sides_search', array( $this, 'handle_search' ) );
		add_action( 'wp_ajax_apb_sides_search', array( $this, 'handle_search' ) );
	}

	public function handle_search(): void {
		check_ajax_referer( 'apb_sides_frontend', 'nonce' );

		$filters = array(
			'keyword'      => isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '',
			'casting_type' => isset( $_POST['casting_type'] ) ? sanitize_text_field( wp_unslash( $_POST['casting_type'] ) ) : '',
			'show'         => isset( $_POST['show'] ) ? absint( wp_unslash( $_POST['show'] ) ) : 0,
			'genre'        => isset( $_POST['genre'] ) ? sanitize_text_field( wp_unslash( $_POST['genre'] ) ) : '',
			'medium'       => isset( $_POST['medium'] ) ? sanitize_text_field( wp_unslash( $_POST['medium'] ) ) : '',
			'gender'       => isset( $_POST['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['gender'] ) ) : '',
		);

		$page = isset( $_POST['page'] ) ? max( 1, absint( wp_unslash( $_POST['page'] ) ) ) : 1;

		$side_repo   = new APB_Sides_Side_Repo();
		$script_repo = new APB_Sides_Script_Repo();

		$per_page = 12;
		$rows     = $side_repo->search_published( $filters, $page, $per_page );
		$total    = $side_repo->count_published( $filters );
		$pages    = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

		$results = array();
		foreach ( $rows as $r ) {
			$script    = $script_repo->get_by_id( (int) $r['script_id'] );
			$item      = $r;
			$item['script_title'] = $script['title'] ?? '';
			$results[] = $item;
		}

		wp_send_json_success(
			array(
				'results'      => $results,
				'total'        => $total,
				'pages'        => $pages,
				'current_page' => $page,
			)
		);
	}
}
