<?php
/**
 * Admin sides list and edit.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sides admin UI.
 */
class APB_Sides_Admin_Sides {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'apb-sides-database' ) );
		}

		$repo       = new APB_Sides_Side_Repo();
		$script_r   = new APB_Sides_Script_Repo();
		$char_r     = new APB_Sides_Character_Repo();
		$pivot_r    = new APB_Sides_Side_Character_Repo();
		$id         = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;

		if ( $id > 0 ) {
			$row = $repo->get_by_id( $id );
			if ( ! $row ) {
				echo '<div class="wrap"><p>' . esc_html__( 'Side not found.', 'apb-sides-database' ) . '</p></div>';
				return;
			}
			$script     = $script_r->get_by_id( (int) $row['script_id'] );
			$chars      = $char_r->get_by_script_id( (int) $row['script_id'] );
			$selected   = $pivot_r->get_characters_for_side( $id );
			$sel_ids    = array_map(
				static function ( $c ) {
					return (int) $c['id'];
				},
				$selected
			);
			$data = array(
				'side'       => $row,
				'script'     => $script,
				'characters' => $chars,
				'selected'   => $sel_ids,
			);
			include APB_SIDES_PLUGIN_DIR . 'templates/admin/side-edit.php';
			return;
		}

		$script_filter = isset( $_GET['script_id'] ) ? absint( wp_unslash( $_GET['script_id'] ) ) : 0;
		$char_filter   = isset( $_GET['character_id'] ) ? absint( wp_unslash( $_GET['character_id'] ) ) : 0;
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$paged         = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$per           = 20;
		$offset        = ( $paged - 1 ) * $per;

		global $wpdb;
		$table  = $wpdb->prefix . 'apb_sides_sides';
		$where  = array( '1=1' );
		$params = array();

		if ( $script_filter > 0 ) {
			$where[]  = 'script_id = %d';
			$params[] = $script_filter;
		}
		if ( $char_filter > 0 ) {
			$pivot_t = $wpdb->prefix . 'apb_sides_side_characters';
			$where[] = 'id IN (SELECT side_id FROM ' . $pivot_t . ' WHERE character_id = %d)';
			$params[] = $char_filter;
		}
		if ( $status_filter !== '' ) {
			$where[]  = 'status = %s';
			$params[] = $status_filter;
		}

		$sql_count = "SELECT COUNT(*) FROM {$table} WHERE " . implode( ' AND ', $where );
		$sql_rows  = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY id DESC LIMIT %d OFFSET %d';

		if ( count( $params ) > 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $wpdb->prepare( $sql_count, $params ) );
			$params_rows = $params;
			$params_rows[] = $per;
			$params_rows[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_results( $wpdb->prepare( $sql_rows, $params_rows ), ARRAY_A );
		} else {
			$total = (int) $wpdb->get_var( $sql_count );
			$rows  = $wpdb->get_results( $wpdb->prepare( $sql_rows, $per, $offset ), ARRAY_A );
		}

		$rows = is_array( $rows ) ? $rows : array();

		$script_titles = array();
		foreach ( $rows as $r ) {
			$sid = (int) $r['script_id'];
			if ( ! isset( $script_titles[ $sid ] ) ) {
				$sc = $script_r->get_by_id( $sid );
				$script_titles[ $sid ] = $sc['title'] ?? '';
			}
		}

		$data = array(
			'sides'          => $rows,
			'scripts'        => $script_r->get_all( array( 'limit' => 500, 'offset' => 0, 'orderby' => 'title', 'order' => 'ASC' ) ),
			'characters'     => $char_r->get_all( array( 'limit' => 500, 'offset' => 0, 'orderby' => 'name', 'order' => 'ASC' ) ),
			'script_id'      => $script_filter,
			'character_id'   => $char_filter,
			'status'         => $status_filter,
			'total'          => $total,
			'per_page'       => $per,
			'current_page'   => $paged,
			'script_titles'  => $script_titles,
		);

		include APB_SIDES_PLUGIN_DIR . 'templates/admin/sides.php';
	}
}
