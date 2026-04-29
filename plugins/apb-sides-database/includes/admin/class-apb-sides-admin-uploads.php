<?php
/**
 * Admin uploads page.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uploads UI.
 */
class APB_Sides_Admin_Uploads {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'apb-sides-database' ) );
		}

		$repo    = new APB_Sides_Upload_Repo();
		$uploads = $repo->get_all( array( 'limit' => 200, 'offset' => 0, 'orderby' => 'id', 'order' => 'DESC' ) );

		foreach ( $uploads as &$row ) {
			$row['raw_text_length']   = 0;
			$row['parse_status_note'] = '';
		}
		unset( $row );

		$data = array(
			'uploads' => $uploads,
		);

		include APB_SIDES_PLUGIN_DIR . 'templates/admin/uploads.php';
	}
}
