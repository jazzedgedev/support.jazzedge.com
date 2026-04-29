<?php
/**
 * Admin dashboard page.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard metrics.
 */
class APB_Sides_Admin_Dashboard {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'apb-sides-database' ) );
		}

		global $wpdb;

		$upload_repo  = new APB_Sides_Upload_Repo();
		$script_repo  = new APB_Sides_Script_Repo();
		$side_repo    = new APB_Sides_Side_Repo();
		$db_tables    = array();
		$table_slugs  = array(
			'apb_sides_uploads',
			'apb_sides_scripts',
			'apb_sides_characters',
			'apb_sides_sides',
			'apb_sides_side_characters',
		);
		foreach ( $table_slugs as $slug ) {
			$full_name = $wpdb->prefix . $slug;
			$exists    = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full_name ) ) === $full_name;
			$count     = 'N/A';
			if ( $exists ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from fixed whitelist.
				$count = (string) (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$full_name}`" );
			}
			$db_tables[] = array(
				'name'   => $full_name,
				'exists' => $exists,
				'count'  => $count,
			);
		}

		$data = array(
			'total_uploads'     => $upload_repo->count(),
			'published_scripts' => $script_repo->count( array( 'status' => APB_Sides_Statuses::PUBLISHED ) ),
			'published_sides'   => $side_repo->count( array( 'status' => APB_Sides_Statuses::PUBLISHED ) ),
			'db_tables'         => $db_tables,
		);

		include APB_SIDES_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}
}
