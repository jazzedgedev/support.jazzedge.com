<?php
/**
 * Admin characters list and edit.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Characters admin UI.
 */
class APB_Sides_Admin_Characters {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'apb-sides-database' ) );
		}

		$repo      = new APB_Sides_Character_Repo();
		$script_r  = new APB_Sides_Script_Repo();
		$id        = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;

		if ( $id > 0 ) {
			$row = $repo->get_by_id( $id );
			if ( ! $row ) {
				echo '<div class="wrap"><p>' . esc_html__( 'Character not found.', 'apb-sides-database' ) . '</p></div>';
				return;
			}
			$script = $script_r->get_by_id( (int) $row['script_id'] );
			$data   = array(
				'character' => $row,
				'script'    => $script,
			);
			include APB_SIDES_PLUGIN_DIR . 'templates/admin/character-edit.php';
			return;
		}

		$script_filter = isset( $_GET['script_id'] ) ? absint( wp_unslash( $_GET['script_id'] ) ) : 0;
		$search        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged         = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$per           = 20;
		$offset        = ( $paged - 1 ) * $per;

		$args = array(
			'limit'   => $per,
			'offset'  => $offset,
			'orderby' => 'id',
			'order'   => 'DESC',
		);
		if ( $script_filter > 0 ) {
			$args['script_id'] = $script_filter;
		}
		if ( $search !== '' ) {
			$args['search'] = $search;
		}

		$rows  = $repo->get_all( $args );
		$total = $repo->count( $args );

		$data = array(
			'characters'   => $rows,
			'scripts'      => $script_r->get_all( array( 'limit' => 500, 'offset' => 0, 'orderby' => 'title', 'order' => 'ASC' ) ),
			'script_id'    => $script_filter,
			'search'       => $search,
			'total'        => $total,
			'per_page'     => $per,
			'current_page' => $paged,
		);

		include APB_SIDES_PLUGIN_DIR . 'templates/admin/characters.php';
	}
}
