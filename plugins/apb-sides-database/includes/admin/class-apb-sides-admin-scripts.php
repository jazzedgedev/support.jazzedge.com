<?php
/**
 * Admin scripts list and edit.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scripts admin UI.
 */
class APB_Sides_Admin_Scripts {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'apb-sides-database' ) );
		}

		$repo = new APB_Sides_Script_Repo();
		$id   = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;

		if ( $id > 0 ) {
			$script = $repo->get_by_id( $id );
			if ( ! $script ) {
				echo '<div class="wrap"><p>' . esc_html__( 'Script not found.', 'apb-sides-database' ) . '</p></div>';
				return;
			}
			$data = array( 'script' => $script );
			include APB_SIDES_PLUGIN_DIR . 'templates/admin/script-edit.php';
			return;
		}

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged  = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$per    = 20;
		$offset = ( $paged - 1 ) * $per;

		if ( $search !== '' ) {
			$scripts = $repo->search( $search );
			$total   = count( $scripts );
		} else {
			$scripts = $repo->get_all(
				array(
					'limit'   => $per,
					'offset'  => $offset,
					'orderby' => 'id',
					'order'   => 'DESC',
				)
			);
			$total = $repo->count();
		}

		$data = array(
			'scripts'      => $scripts,
			'total'        => $total,
			'per_page'     => $per,
			'current_page' => $paged,
			'search'       => $search,
		);

		include APB_SIDES_PLUGIN_DIR . 'templates/admin/scripts.php';
	}
}
